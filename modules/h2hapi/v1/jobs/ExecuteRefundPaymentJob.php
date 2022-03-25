<?php

namespace app\modules\h2hapi\v1\jobs;

use app\services\payment\models\PaySchet;
use app\services\PaymentService;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ExecuteRefundPaymentJob extends BaseObject implements JobInterface
{
    /**
     * @var int
     */
    public $refundPaySchetId;

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function execute($queue)
    {
        $refundPaySchet = PaySchet::findOne($this->refundPaySchetId);

        /** @var PaymentService $service */
        $service = \Yii::$app->get(PaymentService::class);
        $service->executRefundPayment($refundPaySchet);
    }
}