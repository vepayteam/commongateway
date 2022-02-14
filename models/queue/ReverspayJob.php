<?php

namespace app\models\queue;

use app\services\payment\models\PaySchet;
use app\services\PaymentService;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * @deprecated Вместо ReverspayJob использовать RefundPayJob
 */
class ReverspayJob extends BaseObject implements JobInterface
{
    /**
     * @var int
     */
    public $idpay;

    /**
     * @var string|null
     */
    public $initiator;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        if ($this->initiator) {
            Yii::info("ReversPayJob start ID={$this->idpay} Initiator={$this->initiator}");
        }

        Yii::info('ReversePayJob execute ID=' . $this->idpay);
        $paySchet = PaySchet::findOne(['ID' => $this->idpay]);

        /** @var PaymentService $service */
        $service = Yii::$app->get(PaymentService::class);

        try {
            $reversePaySchet = $service->createRefundPayment($paySchet);
            $service->reverse($reversePaySchet);
        } catch (\Exception $e) {
            Yii::error(['ReversPayJob reverse exception', $e]);
        }
    }
}
