<?php


namespace app\services\payment\forms\mts;


use yii\base\Model;

class ConfirmPayRequest extends BaseRequest
{
    public $mdOrder;
    public $paRes;
}
