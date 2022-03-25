<?php

namespace app\services\payment\jobs;

use app\services\payment\models\PaySchet;
use app\services\PaymentService;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class RefundPayJob extends BaseObject implements JobInterface
{
    /**
     * @var int
     */
    public $paySchetId;

    /**
     * @var string|null
     */
    public $initiator;

    /**
     * Сумма в копейках/центах {@see \app\services\payment\helpers\PaymentHelper::convertToPenny()}
     *
     * @var int|null
     */
    public $refundSum;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        if ($this->initiator) {
            Yii::info("RefundPayJob start ID={$this->paySchetId} Initiator={$this->initiator}");
        }

        Yii::info('RefundPayJob execute ID=' . $this->paySchetId);
        $paySchet = PaySchet::findOne(['ID' => $this->paySchetId]);

        /** @var PaymentService $service */
        $service = Yii::$app->get(PaymentService::class);

        try {
            $service->refund($paySchet, $this->refundSum);
        } catch (\Exception $e) {
            Yii::error(['RefundPayJob refund exception', $e]);
        }
    }
}
