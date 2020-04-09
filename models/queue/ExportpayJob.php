<?php

namespace app\models\queue;

//use app\models\protocol\OnlineProv;
use yii\base\BaseObject;

class ExportpayJob extends BaseObject implements \yii\queue\JobInterface
{
    public $idpay;

    public function execute($queue)
    {
        /*$exportpay = new OnlineProv();
        $exportpay->makePay($this->idpay);*/
    }
}