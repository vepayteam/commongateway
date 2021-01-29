<?php


namespace app\services\payment\jobs;


use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\models\PaySchet;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;

class RefundPayJob extends BaseObject implements \yii\queue\JobInterface
{
    public $paySchetId;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        Yii::warning('RefundPayJob execute: ID='.$this->paySchetId);
        $paySchet = PaySchet::findOne(['ID' => $this->paySchetId]);

        $refundPayForm = new RefundPayForm();
        $refundPayForm->paySchet = $paySchet;

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($paySchet->partner, $paySchet->uslugatovar);

        $refundPayResponse = $bankAdapterBuilder->getBankAdapter()->refundPay($refundPayForm);

        if($refundPayResponse->status == BaseResponse::STATUS_DONE) {
            $paySchet->Status = PaySchet::STATUS_CANCEL;
            $paySchet->ErrorInfo = 'Платеж отменен';
            if($paySchet->save(false)) {
                Yii::warning('RefundPayJob refund: ID='.$this->paySchetId);
            } else {
                Yii::error('RefundPayJob error save: ID='.$this->paySchetId);
            }
        } else {

        }

        Yii::warning(
            sprintf(
                'RefundPayJob result: ID=%s, result=%s',
                $this->paySchetId,
                Json::encode($refundPayResponse->getAttributes())
            ),
            'RefundPayJob'
        );
    }
}
