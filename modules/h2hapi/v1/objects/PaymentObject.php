<?php

namespace app\modules\h2hapi\v1\objects;

use app\components\api\ApiObject;
use app\services\payment\models\PaySchet;

class PaymentObject extends ApiObject
{
    /**
     * @var PaymentAcsRedirectObject|null 3DS data.
     */
    public $acsRedirect;
    /**
     * @var string User IP.
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
            'acsRedirect',
        ];
    }

    /**
     * @param PaySchet $paySchet
     * @return $this
     */
    public function mapPaySchet(PaySchet $paySchet): PaymentObject
    {
        $this->card = (new PaymentCardObject())->mapPaySchet($paySchet);

        if ($paySchet->acsRedirect !== null) {
            $this->acsRedirect = (new PaymentAcsRedirectObject)
                ->mapPaySchetAcsRedirect($paySchet->acsRedirect);
        }

        return $this;
    }
}