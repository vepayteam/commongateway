<?php

namespace app\modules\mfo\models;

use app\models\payonline\Partner;
use app\models\Report;
use app\services\payment\models\UslugatovarType;
use app\services\ReportService;
use app\services\reportService\dataObjects\CreateReportData;
use Carbon\Carbon;
use yii\base\Model;

/**
 * Форма создания реестра.
 */
class CreateReportForm extends Model implements CreateReportData
{
    /**
     * @var Partner
     */
    private $partner;

    /**
     * @var string Date of transactions which should be in the report. Format "Y-m-d", e.g. "2021-12-12".
     */
    public $date;
    /**
     * @var string Status of transactions which should be in the report. See {@see Report::TRANSACTION_STATUSES}
     */
    public $transactionStatus;
    /**
     * @var int|array Service type IDs. See {@see UslugatovarType}
     */
    public $serviceType;

    /**
     * @param Partner $partner A merchant used in validation.
     */
    public function __construct(Partner $partner)
    {
        parent::__construct();
        $this->partner = $partner;
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        /** @var ReportService $reportService */
        $reportService = \Yii::$app->get(ReportService::class);

        return [
            [['date'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['transactionStatus'], 'in', 'range' => Report::TRANSACTION_STATUSES, 'strict' => true],
            [
                ['serviceType'], 'in',
                'allowArray' => true,
                'range' => $reportService->getAllowedServiceTypeIds($this->partner),
                'message' => 'Недопустимый тип услуги.',
            ],
        ];
    }


    /**
     * {@inheritDoc}
     */
    public function getDate(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->date);
    }

    /**
     * {@inheritDoc}
     */
    public function getTransactionStatus(): ?int
    {
        return is_numeric($this->transactionStatus) ? $this->transactionStatus : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceTypeIds(): ?array
    {
        return !empty($this->serviceType) ? (array)$this->serviceType : null;
    }
}