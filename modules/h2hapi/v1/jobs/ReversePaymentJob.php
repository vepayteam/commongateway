<?php

namespace app\modules\h2hapi\v1\jobs;

use app\services\payment\models\PaySchet;
use app\services\PaymentService;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ReversePaymentJob extends BaseObject implements JobInterface
{
    /**
     * @var int
     */
    public $paySchetId;

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function execute($queue)
    {
        $paySchet = PaySchet::findOne($this->paySchetId);

        /** @var PaymentService $service */
        $service = \Yii::$app->get(PaymentService::class);
        $service->reverse($paySchet);
    }
}