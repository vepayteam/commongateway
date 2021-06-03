<?php


namespace app\services\payment\forms;


use app\models\payonline\Cards;
use app\services\payment\models\PaySchet;
use Yii;
use yii\base\Model;

class CreatePaySecondStepForm extends Model
{
    /** @var PaySchet */
    protected $paySchet;
    public $IdPay;


    /**
     * @return PaySchet
     */
    public function getPaySchet()
    {
        if(!$this->paySchet) {
            $this->paySchet = PaySchet::findOne(['ID' => $this->IdPay]);
        }
        return $this->paySchet;
    }

    /**
     * URL завершения оплаты по PCIDSS
     *
     * @param $id
     * @return string
     */
    public function getReturnUrl()
    {
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/pay/orderdone?id='. $this->IdPay;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://'.$_SERVER['SERVER_NAME'].'/pay/orderdone?id=' . $this->IdPay;
        } else {
            return 'https://api.vepay.online/pay/orderdone?id=' . $this->IdPay;
        }
    }

}
