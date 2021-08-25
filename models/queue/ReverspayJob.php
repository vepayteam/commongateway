<?php


namespace app\models\queue;


use app\models\bank\BankMerchant;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\mfo\statements\ReceiveStatemets;
use app\models\payonline\Partner;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PaySchet;
use Yii;
use yii\base\BaseObject;
use yii\db\Exception;

class ReverspayJob extends BaseObject implements \yii\queue\JobInterface
{
    public $idpay;

    /**
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        $paySchet = PaySchet::findOne(['ID' => $this->idpay]);

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank, $paySchet->currency);

        $refundPayForm = new RefundPayForm();
        $refundPayForm->paySchet = $paySchet;
        $refundResponse = $bankAdapterBuilder->getBankAdapter()->refundPay($refundPayForm);

        // TODO: перписать на сервисы
        if($refundResponse->status == BaseResponse::STATUS_DONE) {
            $payschets = new Payschets();
            $payschets->SetReversPay($paySchet->ID);
        } else {
            throw new \Exception('ReverspayJob Ошибка возврата ID=' . $paySchet->ID);
        }
    }
}
