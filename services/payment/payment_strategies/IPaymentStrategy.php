<?php


namespace app\services\payment\payment_strategies;


use app\models\bank\TcbGate;
use app\models\kfapi\KfRequest;

interface IPaymentStrategy
{
    public function __construct(KfRequest $kfRequest);
    public function exec();
}
