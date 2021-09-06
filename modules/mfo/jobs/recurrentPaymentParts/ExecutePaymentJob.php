<?php

namespace app\modules\mfo\jobs\recurrentPaymentParts;

use app\services\payment\models\PaySchet;
use app\services\RecurrentPaymentPartsService;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Задача в очереди для осуществления оплаты.
 */
class ExecutePaymentJob extends BaseObject implements JobInterface
{
    private const UPDATE_STATUS_DELAY = 60 * 5;

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
        if ($paySchet === null) {
            throw new \Exception("PaySchet (ID:{$this->paySchetId}) not found.");
        }

        /** @var RecurrentPaymentPartsService $service */
        $service = \Yii::$app->get(RecurrentPaymentPartsService::class);
        $service->executePayment($paySchet);

        $queue
            ->delay(self::UPDATE_STATUS_DELAY)
            ->push(new UpdatePaymentStatusJob(['paySchetId' => $paySchet->ID]));
    }
}