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
use app\services\payment\PaymentService;
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
        $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank);

        $refundPayForm = new RefundPayForm();
        $refundPayForm->paySchet = $paySchet;
        $refundResponse = $bankAdapterBuilder->getBankAdapter()->refundPay($refundPayForm);

        if($refundResponse->status == BaseResponse::STATUS_DONE) {
            $this->getPaymentService()->doneReversPay($paySchet);
        } else {
            throw new \Exception('ReverspayJob Ошибка возврата ID=' . $paySchet->ID);
        }
    }

    /**
     * @return PaymentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getPaymentService()
    {
        return Yii::$container->get('PaymentService');
    }
}
