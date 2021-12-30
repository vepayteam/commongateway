<?php

namespace app\models;

use app\services\payment\models\UslugatovarType;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Transaction report generated upon merchant's request.
 *
 * @property int $Id
 * @property int $PartnerId
 * @property int $Status Report filling up status: 0 - in process, 1 - completed, 2 - error
 * @property string $Date Date of transactions which should be in the report
 * @property int|null $TransactionStatus Status of transactions which should be in the report
 * @property int $CreatedAt Creation timestamp
 * @property int|null $CompletedAt Timestamp when the report was filled up
 *
 * @property UslugatovarType[] $serviceTypes List of service types for the report
 * @property ReportTransaction[] $reportTransactions Transactions in the report (there are many of them, add limit)
 */
class Report extends ActiveRecord
{
    public const STATUS_IN_PROCESS = 0;
    public const STATUS_COMPLETED = 1;
    public const STATUS_ERROR = 2;

    public const TRANSACTION_STATUS_IN_PROCESS = 0;
    public const TRANSACTION_STATUS_SUCCESS = 1;
    public const TRANSACTION_STATUS_FAIL = 2;
    public const TRANSACTION_STATUS_CANCEL = 3;

    public const TRANSACTION_STATUSES = [
        self::TRANSACTION_STATUS_IN_PROCESS,
        self::TRANSACTION_STATUS_SUCCESS,
        self::TRANSACTION_STATUS_FAIL,
        self::TRANSACTION_STATUS_CANCEL,
    ];

    /**
     * {@inheritDoc}
     */
    public static function tableName(): string
    {
        return 'report';
    }

    public function getServiceTypes(): ActiveQuery
    {
        return $this->hasMany(UslugatovarType::class, ['Id' => 'ServiceTypeId'])
            ->viaTable('report_to_service_type_link', ['ReportId' => 'Id']);
    }

    public function getReportTransactions(): ActiveQuery
    {
        return $this->hasMany(ReportTransaction::class, ['ReportId' => 'Id']);
    }
}