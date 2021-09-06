<?php

namespace app\modules\mfo\jobs\recurrentPaymentParts;

use app\services\payment\models\PaySchet;
use app\services\RecurrentPaymentPartsService;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class UpdatePaymentStatusJob extends BaseObject implements JobInterface
{
    private const UPDATE_STATUS_DELAY = 60 * 5;

    /**
     * @var int
     */
    public $paySchetId;

    /**
     * @param Queue $queue
     * @return mixed|void
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
        $status = $service->updatePaymentStatus($paySchet);

        if ($status == PaySchet::STATUS_WAITING && $paySchet->isNeedContinueRefreshStatus()) {
            $queue
                ->delay(self::UPDATE_STATUS_DELAY)
                ->push(new static(['paySchetId' => $paySchet->ID]));
        }
    }
}