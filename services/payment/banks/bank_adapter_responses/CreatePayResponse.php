<?php


namespace app\services\payment\banks\bank_adapter_responses;


use app\services\base\traits\Fillable;
use app\services\payment\interfaces\Issuer3DSVersionInterface;
use Yii;
use yii\base\Model;

class CreatePayResponse extends BaseResponse
{
    use Fillable;

    public $isNeed3DSRedirect = true;

    public $vesion3DS = Issuer3DSVersionInterface::V_1;
    public $isNeed3DSVerif = true;
    public $isNeedSendTransIdTKB = false;
    public $authValue;
    public $dsTransId;
    public $eci;
    public $cardRefId;

    public $transac;
    public $url;
    public $pa;
    public $md;
    public $fatal;
    public $termurl;
    public $doneurl;
    public $creq;
    public $threeDSServerTransID = '';
    public $threeDSMethodURL;
    public $html3dsForm;
    public $params3DS;

    /**
     * @param int $paySchetId
     * @return string
     */
    public function getRetUrl($paySchetId)
    {
        return Yii::$app->params['domain'] . '/pay/orderdone/'.$paySchetId;
    }

    /**
     * @param int $paySchetId
     * @return string
     */
    public function getStep2Url($paySchetId)
    {
        return Yii::$app->params['domain'] . '/pay/createpay-second-step/'.$paySchetId;
    }

}
