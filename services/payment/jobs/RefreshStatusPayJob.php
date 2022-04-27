<?php


namespace app\services\payment\jobs;


use app\clients\tcbClient\TcbOrderNotExistException;
use app\models\TU;
use app\modules\partner\services\StopRefreshStatusService;
use app\services\notifications\NotificationsService;
use app\services\payment\forms\OkPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\RefreshStatusPayStrategy;
use Yii;
use yii\base\BaseObject;

class RefreshStatusPayJob extends BaseObject implements \yii\queue\JobInterface
{
    /** @todo Remove, hack for TCB (VPBC-1298). */
    private const TCB_ORDER_NOT_EXIST_INTERVAL = 5 * 60; // 5 minutes
    private const TCB_ORDER_NOT_EXIST_TIMEOUT = 2 * 60 * 60; // 2 hours

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

        $stopRefreshStatusService = new StopRefreshStatusService($paySchet->ID);
        if ($stopRefreshStatusService->isStopRefreshStatus()) {
            Yii::info('RefreshStatusPayJob stop job execution paySchetId=' . $paySchet->ID, 'RefreshStatusPayJob');
            return;
        }

        $okPayForm = new OkPayForm();
        $okPayForm->IdPay = $this->paySchetId;

        $refreshStatusPayStrategy = new RefreshStatusPayStrategy($okPayForm);
        try {
            $paySchet = $refreshStatusPayStrategy->exec();
        } catch (TcbOrderNotExistException $e) {
            /** @todo Remove, hack for TCB (VPBC-1298). */
            $paySchet = $okPayForm->getPaySchet();
            if (time() < $paySchet->DateCreate + self::TCB_ORDER_NOT_EXIST_TIMEOUT) {
                $paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
                $paySchet->ErrorInfo = 'Ожидает запрос статуса';
                $paySchet->save(false);
                $queue
                    ->delay(self::TCB_ORDER_NOT_EXIST_INTERVAL)
                    ->push(new static(['paySchetId' => $paySchet->ID]));
            } else {
                $paySchet->Status = PaySchet::STATUS_ERROR;
                $paySchet->RCCode = PaySchet::RCCODE_CANCEL_PAYMENT;
                $paySchet->ErrorInfo = 'Операция не завершена / пользователь не завершил проверку 3DS';
                $paySchet->save(false);

                /** @var NotificationsService $notificationsService */
                $notificationsService = Yii::$container->get('NotificationsService');
                $notificationsService->addNotificationByPaySchet($paySchet);
            }
        }

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
                    ->push(new static([
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
