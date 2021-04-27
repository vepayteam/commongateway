<?php


namespace app\services\payment\banks\bank_adapter_responses;


use app\services\payment\interfaces\Issuer3DSVersionInterface;
use Yii;
use yii\base\Model;

class CreatePayResponse extends BaseResponse
{
    public $isNeed3DSRedirect = true;

    public $vesion3DS = Issuer3DSVersionInterface::V_1;
    public $isNeed3DSVerif = true;
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

    /**
     * @param int $paySchetId
     * @return string
     */
    public function getRetUrl($paySchetId)
    {
        return Yii::$app->params['domain'] . '/pay/orderdone/'.$paySchetId;
    }

}
