<?php


namespace app\services\payment\jobs;


use app\helpers\DebugHelper;
use app\models\TU;
use app\services\notifications\NotificationsService;
use app\services\payment\forms\OkPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\RefreshStatusPayStrategy;
use Yii;
use yii\base\BaseObject;

class RefreshStatusPayJob extends BaseObject implements \yii\queue\JobInterface
{
    public $paySchetId;

    /**
     * @var int|null Refresh interval in seconds.
     */
    public $interval = null;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        Yii::warning("RefreshStatusPayJob execute ID={$this->paySchetId}", 'RefreshStatusPayJob');
        $paySchet = PaySchet::findOne(['ID' => $this->paySchetId]);

        Yii::warning('RefreshStatusPayJob execute isHavePayschet=' . !empty($paySchet), 'RefreshStatusPayJob');
        Yii::warning('RefreshStatusPayJob execute paySchetId=' . $paySchet->ID, 'RefreshStatusPayJob');

        $okPayForm = new OkPayForm();
        $okPayForm->IdPay = $this->paySchetId;

        $refreshStatusPayStrategy = new RefreshStatusPayStrategy($okPayForm);
        $paySchet = $refreshStatusPayStrategy->exec();

        if($paySchet->Status == PaySchet::STATUS_WAITING) {
            $paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
            $paySchet->ErrorInfo = 'Ожидает запрос статуса';
            $paySchet->save(false);

            if($paySchet->isNeedContinueRefreshStatus()) {
                // TODO пока костыль
                $delay = 5 * 60; // 5 min
                if (TU::IsInAutoAll($paySchet->uslugatovar->IsCustom)) {
                    $delay = 120 * 60; // 120 min
                }

                Yii::$app->queue
                    ->delay($this->interval ?? $delay)
                    ->push(new RefreshStatusPayJob([
                        'paySchetId' => $paySchet->ID,
                        'interval' => $this->interval,
                    ]));
            }
        }

        Yii::warning(
            sprintf(
                'RefreshStatusPayJob result: ID=%s',
                $this->paySchetId
            ),
            'RefreshStatusPayJob'
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
