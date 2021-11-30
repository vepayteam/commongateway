<?php

namespace app\services\payment\payment_strategies\mfo;

use app\models\payonline\Cards;
use app\services\base\exceptions\InvalidInputParamException;
use app\services\payment\forms\MfoCallbackForm;
use app\services\payment\models\PaySchet;

class MfoPayLkCallbackStrategy
{
    private $callbackForm;

    public function __construct(MfoCallbackForm $callbackForm)
    {
        $this->callbackForm = $callbackForm;
    }

    /**
     * @return PaySchet
     * @throws InvalidInputParamException
     */
    public function exec(): PaySchet
    {
        $paySchet = PaySchet::findOne(['ID' => $this->callbackForm->order_id]);

        if (!$paySchet) {
            throw new InvalidInputParamException('cant find payschet. id: '.$this->callbackForm->order_id);
        }

        /** @var Cards $card */
        $card = Cards::findOne(['ID' => $paySchet->IdKard]);

        if($card->CardNumber === $this->callbackForm->card && ($card->ExtCardIDP !== $this->callbackForm->cardToken || in_array($card->ExtCardIDP, [0, null, ''], true))) {
            $card->ExtCardIDP = $this->callbackForm->cardToken;
            $card->save(false);
        }

        return $paySchet;
    }
}
