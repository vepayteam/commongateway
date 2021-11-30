<?php

namespace app\services;

use app\models\payonline\Partner;
use app\models\Report;
use app\models\ReportTransaction;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\reportService\dataObjects\CreateReportData;
use Carbon\Carbon;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
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

            \Yii::$app->errorHandler->logException($e);
            throw $e;
        }

        if ($cleanExpired) {
            $this->cleanExpiredReports();
        }
    }

    /**
     * @throws \yii\db\Exception
     */
    private function fillReportInternal(Report $report)
    {
        \Yii::info("Report (ID: {$report->Id}) filling started.");

        $date = Carbon::createFromFormat('Y-m-d', $report->Date);
        $selectQuery = PaySchet::find()
            ->alias('paySchetAlias')
            ->select([
                'ReportId' => new Expression(':reportId'),
                'TransactionId' => 'paySchetAlias.ID',
                'Status' => '(IF(paySchetAlias.Status IN (0,4,5), 0, paySchetAlias.Status))', // 0,4,5 - in progress (0)
                'ErrorCode' => 'paySchetAlias.RCCode',
                'Error' => 'paySchetAlias.ErrorInfo',
                'ExtId' => 'paySchetAlias.Extid',
                'ProviderBillNumber' => 'paySchetAlias.Extbillnumber',
                'Merchant' => 'partnerAlias.Name',
                'ServiceName' => 'uslugatovarTypeAlias.Name',
                'BasicAmount' => 'ROUND(paySchetAlias.SummPay / 100, 2)',
                'ClientCommission' => 'ROUND(paySchetAlias.ComissSumm / 100, 2)',
                'MerchantCommission' => 'ROUND(paySchetAlias.MerchVozn / 100, 2)',
                'Currency' => 'currencyAlias.Code',
                'CardPan' => 'paySchetAlias.CardNum',
                'CardPaymentSystem' => 'paySchetAlias.CardType',
                'Provider' => 'bankAlias.Name',
                'CreateDateTime' => 'FROM_UNIXTIME(paySchetAlias.DateCreate)',
                'FinishDateTime' => '(IF(paySchetAlias.DateOplat = 0, NULL, FROM_UNIXTIME(paySchetAlias.DateOplat)))',
            ])
            ->joinWith([
                'partner AS partnerAlias',
                'uslugatovar AS uslugatovarAlias',
                'uslugatovar AS uslugatovarAlias' => function (ActiveQuery $query) {
                    $query->joinWith(['type AS uslugatovarTypeAlias']);
                },
                'currency AS currencyAlias',
                'bank AS bankAlias',
            ])
            ->andWhere(['paySchetAlias.IdOrg' => $report->PartnerId])
            ->andWhere(['>=', 'paySchetAlias.DateCreate', $date->startOfDay()->getTimestamp()])
            ->andWhere(['<=', 'paySchetAlias.DateCreate', $date->endOfDay()->getTimestamp()])
            ->params([':reportId' => $report->Id]);
        if ($report->serviceTypes !== []) {
            $selectQuery->andWhere(['in', 'uslugatovarTypeAlias.Id', ArrayHelper::getColumn($report->serviceTypes, 'Id')]);
        }
        if ($report->TransactionStatus !== null) {
            $statuses = [
                Report::TRANSACTION_STATUS_IN_PROCESS => [PaySchet::STATUS_WAITING, PaySchet::STATUS_NOT_EXEC, PaySchet::STATUS_WAITING_CHECK_STATUS],
                Report::TRANSACTION_STATUS_SUCCESS => [PaySchet::STATUS_DONE],
                Report::TRANSACTION_STATUS_FAIL => [PaySchet::STATUS_ERROR],
                Report::TRANSACTION_STATUS_CANCEL => [PaySchet::STATUS_CANCEL],
            ][$report->TransactionStatus];
            $selectQuery->andWhere(['in', 'paySchetAlias.Status', $statuses]);
        }

        \Yii::$app->db->createCommand()->insert(ReportTransaction::tableName(), $selectQuery)->execute();

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