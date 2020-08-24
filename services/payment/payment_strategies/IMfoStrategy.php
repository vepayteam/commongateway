<?php


namespace app\services\payment\payment_strategies;


use app\models\mfo\MfoReq;

interface IMfoStrategy
{
    public function __construct(MfoReq $kfRequest);
    public function exec();
}
