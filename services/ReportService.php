<?php

namespace app\services;

use app\models\payonline\Partner;
use app\models\Report;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\reportService\dataObjects\CreateReportData;
use Carbon\Carbon;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Transaction report service.
 */
class ReportService extends Component
{
    /**
     * @var int Number of transactions per page.
     */
    public $transactionListPageSize = 1000;
    /**
     * @var int Time to keep reports in database. 3 days by default.
     */
    public $reportExpireTimeout = 3 * 24 * 60 * 60;

    /**
     * Creates a transaction report for the specified merchant (partner).
     *
     * @param CreateReportData $data Data transfer object for report creation
     * @param Partner $partner Merchant
     * @return Report
     */
    public function create(CreateReportData $data, Partner $partner): Report
    {
        $report = new Report();
        $report->PartnerId = $partner->ID;
        $report->Status = Report::STATUS_IN_PROCESS;
        $report->Date = $data->getDate()->format('Y-m-d');
        if ($data->getTransactionStatus() !== null) {
            $report->TransactionStatus = $data->getTransactionStatus();
        }
        $report->CreatedAt = time();
        $report->save(false);

        // link service types (uslugatovar types)
        if ($data->getServiceTypeIds() !== null) {
            $types = UslugatovarType::find()
                ->andWhere(['in', 'Id', $data->getServiceTypeIds()])
                ->indexBy('Id')
                ->all();
            foreach ($types as $type) {
                $report->link('serviceTypes', $type);
            }
        }

        \Yii::info("Report (ID: {$report->Id}) created.");

        $report->loadDefaultValues();
        return $report;
    }

    /**
     * Checks creation timeout for specified merchant (partner).
     *
     * @param Partner $partner
     * @param int $timeout Timeout in seconds
     * @return bool
     */
    public function checkCreateTimeout(Partner $partner, int $timeout): bool
    {
        /** @var Report $lastReport */
        $lastReport = Report::find()
            ->andWhere(['PartnerId' => $partner->ID])
            ->orderBy(['CreatedAt' => SORT_DESC])
            ->limit(1)
            ->one();
        if ($lastReport !== null) {
            return $lastReport->CreatedAt < (time() - $timeout);
        }

        return true;
    }

    /**
     * Returns a list of service type IDs which allowed for the specified merchant (partner) to create a report with.
     *
     * @param Partner $partner
     * @return array
     */
    public function getAllowedServiceTypeIds(Partner $partner): array
    {
        return array_values(array_unique(ArrayHelper::getColumn($partner->getUslugatovars()->all(), 'IsCustom')));
    }

    /**
     * Fills the specified report with transaction items.
     *
     * Also cleans expired reports by default.
     *
     * @param Report $report
     * @param bool $cleanExpired
     * @throws \Throwable
     */
    public function fillReport(Report $report, bool $cleanExpired = true)
    {
        try {
            $this->fillReportInternal($report);

            $report->Status = Report::STATUS_COMPLETED;
            $report->CompletedAt = time();
            $report->save(false);
        } catch (\Throwable $e) {
            $report->Status = Report::STATUS_ERROR;
            $report->CompletedAt = time();
            $report->save(false);

            throw $e;
        }

        if ($cleanExpired) {
            $this->cleanExpiredReports();
        }
    }

    private function fillReportInternal(Report $report)
    {
        \Yii::info("Report (ID: {$report->Id}) filling started.");

        $date = Carbon::createFromFormat('Y-m-d', $report->Date);
        $params = [
            'reportId' => $report->Id,
            'partnerId' => $report->PartnerId,
            'startTimestamp' => $date->startOfDay()->getTimestamp(),
            'endTimestamp' => $date->endOfDay()->getTimestamp(),
        ];

        $where = '';
        if ($report->serviceTypes !== []) {
            $typeIds = join(',', array_unique(array_map('intval', ArrayHelper::getColumn($report->serviceTypes, 'Id'))));
            $where .= " AND u.Id IN ({$typeIds})";
        }
        if ($report->TransactionStatus !== null) {
            $statuses = join(',', [
                Report::TRANSACTION_STATUS_IN_PROCESS => [PaySchet::STATUS_WAITING, PaySchet::STATUS_NOT_EXEC, PaySchet::STATUS_WAITING_CHECK_STATUS],
                Report::TRANSACTION_STATUS_SUCCESS => [PaySchet::STATUS_DONE],
                Report::TRANSACTION_STATUS_FAIL => [PaySchet::STATUS_ERROR],
                Report::TRANSACTION_STATUS_CANCEL => [PaySchet::STATUS_CANCEL],
            ][$report->TransactionStatus]);
            $where .= " AND p.Status IN ({$statuses})";
        }

        $sql = "
            INSERT INTO report_transaction (
                `ReportId`,
                `TransactionId`,
                `Status`,
                `ErrorCode`,
                `Error`,
                `ExtId`,
                `ProviderBillNumber`,
                `Merchant`,
                `ServiceName`,
                `BasicAmount`,
                `ClientCommission`,
                `MerchantCommission`,
                `Currency`,
                `CardPan`,
                `CardPaymentSystem`,
                `Provider`,
                `CreateDateTime`,
                `FinishDateTime`
            )
            (   
                SELECT
                    :reportId AS 'ReportId',
                    p.ID AS 'TransactionId',
                    -- p.Status AS 'Status',
                    (IF(p.Status IN (0,4,5), 0, p.Status)) AS 'Status', -- 0,4,5 - in progress (0)
                    p.RCCode AS 'ErrorCode',
                    p.ErrorInfo AS 'Error',
                    p.Extid AS 'ExtId',
                    p.Extbillnumber AS 'ProviderBillNumber',
                    pt.Name AS 'Merchant',
                    u.name AS 'ServiceName',
                    ROUND(p.SummPay / 100, 2) AS 'BasicAmount',
                    ROUND (p.ComissSumm / 100, 2) AS 'ClientCommission',
                    ROUND (p.MerchVozn / 100, 2) AS 'MerchantCommission',
                    c.Code AS 'Currency',
                    p.CardNum AS 'CardPan',
                    p.CardType AS 'CardPaymentSystem',
                    b.Name AS 'Provider',
                    FROM_UNIXTIME(p.DateCreate) AS 'CreateDateTime',
                    (IF(p.DateOplat = 0, NULL, FROM_UNIXTIME(p.DateOplat))) AS 'FinishDateTime'
                
                FROM pay_schet p
                
                LEFT JOIN partner pt ON pt.ID = p.IdOrg
                LEFT JOIN uslugatovar ut ON ut.ID = p.IdUsluga
                LEFT JOIN uslugatovar_types u ON u.Id = ut.IsCustom
                LEFT JOIN currency c on p.CurrencyId = c.Id
                LEFT JOIN banks b ON b.ID = p.Bank
                
                WHERE
                    p.IdOrg = :partnerId
                    AND p.DateCreate >= :startTimestamp AND p.DateCreate < :endTimestamp
                    {$where}
            );
        ";

        \Yii::$app->db->createCommand($sql, $params)->execute();

        \Yii::info("Report (ID: {$report->Id}) filling completed.");
    }

    /**
     * Cleans up expired reports.
     *
     * @see $reportExpireTimeout
     */
    public function cleanExpiredReports()
    {
        \Yii::info('Cleaning up expired reports...');

        $expiredReports = Report::find()
            ->andWhere(['<', 'CreatedAt', time() - $this->reportExpireTimeout])
            ->all();
        foreach ($expiredReports as $report) {
            $report->delete();
            \Yii::info("Report (ID: {$report->Id}) removed.");
        }
    }

    /**
     * Paginated list of transactions in the specified report.
     *
     * @param Report $report
     * @param int $page Zero-based page number.
     * @return ActiveDataProvider
     */
    public function getTransactionList(Report $report, int $page): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $report->getReportTransactions(),
            'pagination' => [
                'page' => $page,
                'pageSize' => $this->transactionListPageSize,
            ],
        ]);
    }
}