<?php

namespace app\models\queue;

use app\services\payment\models\PaySchet;
use app\services\PaymentService;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

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
            $service->reverse($paySchet);
        } catch (\Exception $e) {
            Yii::error(['ReversPayJob reverse exception', $e]);
        }
    }
}
