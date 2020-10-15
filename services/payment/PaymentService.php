<?php


namespace app\services\payment;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfRequest;
use app\models\partner\admin\VyvodParts;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\payonline\Provparams;
use app\models\payonline\Uslugatovar;
use app\models\PayschetPart;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\payment_strategies\CreateFormEcomStrategy;
use app\services\payment\payment_strategies\CreateFormJkhStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use app\services\payment\traits\PayPartsTrait;
use Carbon\Carbon;
use Yii;
use yii\db\Query;

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
