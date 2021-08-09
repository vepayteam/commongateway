<?php

namespace app\modules\hhapi\v1\objects;

use app\services\payment\models\PaySchet;
use yii\base\Model;

/**
 * Статус Счета.
 */
class InvoiceStatusObject extends Model
{
    /**
     * @var int ID статуса.
     */
    public $id;

    /**
     * @var string Название статуса.
     */
    public $name;

    /**
     * @var string Ошибка (может отсутствовать).
     */
    public $errorInfo;

    /**
     * @var string Код ошибки банка (может отсутствовать).
     */
    public $bankErrorCode;

    /**
     * @var string Банк-эквайер, через который была совершена транзакция.
     */
    public $bank;

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            'id',
            'name',
            'errorInfo',
            'bankErrorCode',
            'bank',
        ];
    }

    /**
     * @param PaySchet $paySchet
     * @return $this
     */
    public function mapPaySchet(PaySchet $paySchet): InvoiceStatusObject
    {
        $this->id = $paySchet->Status;
        $this->bank = $paySchet->bank->ChannelName;
        $this->name = PaySchet::STATUSES[$paySchet->Status] ?? null;
        $this->bankErrorCode = !empty($paySchet->RCCode) ? $paySchet->RCCode : null;
        $this->errorInfo = $paySchet->ErrorInfo;

        return $this;
    }
}