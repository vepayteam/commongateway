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
     * @var string Статус.
     */
    public $message;

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
            'message',
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

        if ($paySchet->Status == PaySchet::STATUS_WAITING) {
            $this->message = 'В обработке';
            $this->bankErrorCode = null;
        } else {
            $this->message = (string)$paySchet->ErrorInfo;
            $this->bankErrorCode = $paySchet->RCCode;
        }

        return $this;
    }
}