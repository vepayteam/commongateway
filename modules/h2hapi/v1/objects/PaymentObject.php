<?php

namespace app\modules\h2hapi\v1\objects;

use app\components\api\ApiObject;
use app\services\payment\models\PaySchet;

/**
 * Платеж по Счету.
 */
class PaymentObject extends ApiObject
{
    /**
     * @var string URL для прохождения проверки 3DS.
     */
    public $acsUrl;

    /**
     * @var string IP клиента.
     */
    public $ip;

    /**
     * @var PaymentCardObject
     */
    public $card;

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['card', 'ip'], 'required'],
            [['ip'], 'ip'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function fields(): array
    {
        return [
            'card',
            'acsUrl',
        ];
    }

    /**
     * @param PaySchet $paySchet
     * @return $this
     */
    public function mapPaySchet(PaySchet $paySchet): PaymentObject
    {
        $this->card = (new PaymentCardObject())->mapPaySchet($paySchet);

        return $this;
    }
}