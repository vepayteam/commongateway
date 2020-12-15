<?php


namespace app\services\card;

use app\models\api\Reguser;
use app\models\bank\TCBank;
use Yii;
use app\services\card\base\CardBase;
use yii\base\Exception;
use yii\mutex\FileMutex;
use app\models\payonline\CreatePay;

class Info extends CardBase
{
    public function rules()
    {
        return [];
    }

    public function onEvents(): void
    {
        $this->on(self::EVENT_VALIDATE_ERRORS, function ($e) {
            Yii::warning("card/info: " . $this->GetError());
        });
    }

    public function initModel(): void
    {
        echo __FUNCTION__;
    }
}