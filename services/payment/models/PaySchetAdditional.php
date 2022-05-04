<?php

namespace app\services\payment\models;

class PaySchetAdditional extends PaySchet
{
    public $CntPays;
    public $Currency;
    public $VoznagSumm;
    public $NameUsluga;
    public $IsCustom;
    public $count;
    public $RefundAmount;
    public $RemainingRefundAmount;
    public $CardNumber;
}