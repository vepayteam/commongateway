<?php

namespace app\modules\hhapi\v1\objects;

use app\components\api\ApiObject;
use app\services\payment\models\PaySchet;

/**
 * Платеж по Счету.
 */
class PaymentObject extends ApiObject
{
    /**
     * @var string
     */
    public $acsUrl;

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
            [['card'], 'required'],
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