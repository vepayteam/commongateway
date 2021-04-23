<?php


namespace app\services\payment\jobs;


use app\services\notifications\NotificationsService;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\OkPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\RefreshStatusPayStrategy;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;
use yii\queue\Queue;

class RefreshStatusPayJob extends BaseObject implements \yii\queue\JobInterface
{
    public $paySchetId;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        Yii::warning('RefreshStatusPayJob execute: ID='.$this->paySchetId);
        $paySchet = PaySchet::findOne(['ID' => $this->paySchetId]);

        Yii::warning('RefreshStatusPayJob execute isHavePayschet=' . !empty($paySchet));
        Yii::warning('RefreshStatusPayJob execute paySchetId=' . $paySchet->ID);

        $okPayForm = new OkPayForm();
        $okPayForm->IdPay = $this->paySchetId;

        $refreshStatusPayStrategy = new RefreshStatusPayStrategy($okPayForm);
        $paySchet = $refreshStatusPayStrategy->exec();

        Yii::warning(
            sprintf(
                'RefreshStatusPayJob result: ID=%s',
                $this->paySchetId
            ),
            'RefundPayJob'
        );
    }

    /**
     * @return NotificationsService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getNotificationsService()
    {
        return Yii::$container->get('NotificationsService');
    }
}
