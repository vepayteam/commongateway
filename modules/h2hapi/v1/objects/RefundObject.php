<?php

namespace app\modules\h2hapi\v1\objects;

use app\components\api\ApiObject;
use app\services\payment\models\PaySchet;

class RefundObject extends ApiObject
{
    /**
     * @var int Сумма возврата платежа в копейках.
     */
    public $amountFractional = null;

    /**
     * @var int ID {@see PaySchet} возврата.
     */
    public $id;
    /**
     * @var array
     */
    public $status;
    /**
     * @var string
     */
    public $message;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['amountFractional'], 'integer', 'min' => 100, 'max' => 1000000 * 100],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            'amountFractional',
            'id',
            'status',
            'message',
        ];
    }

    /**
     * @param PaySchet $refundPaySchet
     * @return $this
     */
    public function mapRefundPayschet(PaySchet $refundPaySchet): RefundObject
    {
        $this->id = $refundPaySchet->ID;
        $this->status = $refundPaySchet->Status;
        $this->message = $refundPaySchet->ErrorInfo;
        $this->amountFractional = (int)$refundPaySchet->SummPay;
        return $this;
    }
}