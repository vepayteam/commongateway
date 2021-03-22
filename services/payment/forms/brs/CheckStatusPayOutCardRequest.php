<?php


namespace app\services\payment\forms\brs;


use app\services\payment\models\PartnerBankGate;
use yii\base\Model;

class CheckStatusPayOutCardRequest extends Model implements IXmlRequest
{
    use XmlRequestTrait;

    public $target = 'moneytransfer';
    public $operation = 'get_status';
    public $paymentid;
}
