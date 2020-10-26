<?php


namespace app\services\payment\banks\bank_adapter_responses;


use Yii;
use yii\base\Model;

class CreatePayResponse extends BaseResponse
{
    public $transac;
    public $url;
    public $pa;
    public $md;
    public $fatal;
    public $termurl;

    /**
     * @param int $paySchetId
     * @return string
     */
    public function getRetUrl($paySchetId)
    {
        return Yii::$app->params['domain'] . '/pay/orderdone?id='.$paySchetId;
    }

}
