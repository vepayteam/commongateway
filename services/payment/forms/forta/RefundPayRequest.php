<?php

namespace app\services\payment\forms\forta;

use yii\base\Model;

class RefundPayRequest extends Model
{
    /**
     * @var string Номер платежа в системе сервиса
     */
    public $payment_id;

    /**
     * @var float сумма возврата в рублях, если не передан, то возвращается полная сумма
     */
    public $amount;
}
