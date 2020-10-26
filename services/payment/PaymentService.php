<?php


namespace app\services\payment;


use app\models\bank\BankCheck;
use app\models\kfapi\KfRequest;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\forms\SetPayOkForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\CreateFormEcomStrategy;
use app\services\payment\payment_strategies\CreateFormJkhStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use app\services\payment\traits\PayPartsTrait;
use Carbon\Carbon;
use Yii;
use yii\db\Query;
use yii\mutex\FileMutex;

class PaymentService
{
    use PayPartsTrait;

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

}
