<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Transaction in the corresponding report.
 *
 * @property int $Id
 * @property int $ReportId Foreign key to the report
 * @property int $TransactionId Transaction (payschet) ID
 * @property int $Status Transaction (payschet) status
 * @property string|null $ErrorCode
 * @property string|null $Error Error message
 * @property string|null $ExtId External ID from merchant
 * @property string|null $ProviderBillNumber Bill number from payment provider (bank)
 * @property string|null $Merchant Name of merchant
 * @property string|null $ServiceName Name of service type (uslugatovar type)
 * @property float $BasicAmount Payment sum
 * @property float $ClientCommission Commission paid by client
 * @property float $MerchantCommission Commission paid by merchant
 * @property string $Currency Currency code (ISO 4217)
 * @property string|null $CardPan Masked card number
 * @property string|null $CardPaymentSystem Payment system name, e.g. Visa, Mastercard, Mir
 * @property string|null $Provider Payment provider (bank) name
 * @property string|null $CreateDateTime
 * @property string|null $FinishDateTime
 *
 * @property Report $report
 */
class ReportTransaction extends ActiveRecord
{
    /**
     * {@inheritDoc}
     */
    public static function tableName(): string
    {
        return 'report_transaction';
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            'Id' => 'TransactionId',
            'ReportId',
            'Status',
            'ErrorCode',
            'Error',
            'ExtId',
            'ProviderBillNumber',
            'Merchant',
            'ServiceName',
            'BasicAmount',
            'AuthAmount' => function (ReportTransaction $transaction) {
                return number_format(
                    round($transaction->BasicAmount + $transaction->ClientCommission, 2),
                    2, '.', ''
                );
            },
            'ClientCommission',
            'MerchantCommission',
            'Currency',
            'CardPan',
            'CardPaymentSystem',
            'Provider',
            'CreateDateTime',
            'FinishDateTime',
        ];
    }

    public function getReport(): ActiveQuery
    {
        return $this->hasOne(Report::class, ['Id' => 'ReportId']);
    }
}