<?php


namespace app\services\payment;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\PayschetPart;
use app\services\payment\payment_strategies\CreateFormEcomStrategy;
use app\services\payment\payment_strategies\CreateFormJkhStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use Yii;
use yii\db\Query;

class PaymentService
{

    public function createPay(KfRequest $kfRequest)
    {
        /** @var IPaymentStrategy $paymentStrategy */
        $paymentStrategy = null;
        switch($kfRequest->GetReq('type', 0)) {
            case 1:
                $paymentStrategy = new CreateFormJkhStrategy($kfRequest);
                break;
            default:
                $paymentStrategy = new CreateFormEcomStrategy($kfRequest);
                break;
        }


        return $paymentStrategy->exec();
    }


    public function sendPartsToPartners()
    {
        
        
        
    }

    private function getPartnerWhosNeedSendParts()
    {
        $q = new Query();
        $q->select(['ps.IdOrg'])->from(PayschetPart::tableName());
    }

}
