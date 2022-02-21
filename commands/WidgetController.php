<?php

namespace app\commands;

use app\models\bank\TCBank;
use app\models\bank\WithdrawTCBankIterable;
use app\models\mfo\DistributionReports;
use app\models\payonline\Partner;
use app\models\Payschets;
use app\models\planner\AlarmsSend;
use app\models\planner\BrsReportToEmail;
use app\models\planner\OtchToEmail;
use app\models\planner\ReceiveTelegram;
use app\models\planner\ReturnComisMfo;
use app\models\planner\SendNews;
use app\models\planner\UpdateStatems;
use app\models\planner\VyvodSumPay;
use app\models\planner\VyvodVoznagPlanner;
use app\models\telegram\Telegram;
use app\services\notifications\NotificationsService;
use app\services\payment\models\PaySchet;
use app\services\payment\PaymentService;
use Carbon\Carbon;
use Yii;
use yii\console\Controller;
use app\models\payonline\OrderNotif;
use app\models\planner\CheckpayCron;
use app\models\planner\Notification;
use yii\db\Transaction;
use yii\helpers\Json;
use yii\helpers\VarDumper;

class WidgetController extends Controller
{
    public function init()
    {
        date_default_timezone_set('Europe/Moscow');
        setlocale (LC_TIME, "RUS");

        parent::init();
    }

    /**
     * Proverka statusa platezha po taimautu (1m)
     */
    public function actionRsbcron()
    {
        $startTime = microtime(true);
        Yii::warning('rsbcron Start', 'rsbcron');

        $paycron = new CheckpayCron();
        $paycron->execute();

        echo "Run Rsbcron end\n";

        $this->actionAlarms();

        $this->actionSendNews();
        /*if (date('i')  % 15 == 0) {
            $this->actionUpdateTelegram();
        }*/
        if (date('i')  % 20 == 0) {
            $this->actionReceiveTelegram();
        }

        Yii::warning('rsbcron TimeExec: ' . (microtime(true) - $startTime), 'rsbcron');
        Yii::warning('rsbcron Finish', 'rsbcron');
    }

    /**
     * Obrabotka ocheredi (1m)
     */
    public function actionQueue()
    {
        /* @var $queue \yii\queue\db\Queue */
        $queue = Yii::$app->queue;
        $queue->run(false);

        echo "Run Queue end\n";
    }

    /**
     * Uvedomleniia ob oplate (1m)
     * @throws \yii\db\Exception
     */
    public function actionNotification()
    {
        $notification = new Notification();
        $notification->execute();
        $ordernotif = new OrderNotif();
        $ordernotif->SendEmails();
    }

    public function actionNotificationBlock($idPartner, $startId, $finishId)
    {
        $notification = new Notification();

        $connection = Yii::$app->db;

        $page = 0;

        while(true) {

            $q = '
                SELECT
                    p.ID,
                    n.ID AS IdNotif,
                    n.Email,
                    n.TypeNotif,
                    p.SummPay,
                    p.QrParams,
                    p.Status,
                    us.EmailShablon,
                    us.IsCustom,
                    us.UrlInform,
                    us.KeyInform,
                    p.IdUsluga,
                    p.UserUrlInform,
                    p.UserKeyInform,
                    p.Extid,
                    n.SendCount,
                    p.DateOplat,
                    p.UserEmail
                FROM
                    `notification_pay` AS n
                    JOIN `pay_schet` AS p ON (p.ID = n.IdPay)
                    JOIN uslugatovar AS us ON (us.ID = p.IdUsluga)
                WHERE
                    p.IdOrg = ' . $idPartner . '
                    AND p.ID >= ' . $startId . ' AND p.ID <= ' . $finishId . '
                    AND n.DateSend = 0
                ORDER BY p.ID DESC
                LIMIT ' . 100 . ($page > 0 ? "," . $page * 100 : "") .'  
            ';

            $query = $connection->createCommand($q)->query();

            if($query->count() == 0) {
                break;
            }

            $page++;
            while ($value = $query->read()) {
                if(Yii::$app->cache->get('NotificationBlock' . $value['IdNotif'])) {
                    continue;
                }

                Yii::$app->cache->set('NotificationBlock' . $value['IdNotif'], 1);

                echo "Run Notification ID=" . $value['IdNotif'] . " (" . $value['TypeNotif'] . ") count=" . $value['SendCount'] . "\n";
                Yii::warning("Run Notification ID=" . $value['IdNotif'] . " (" . $value['TypeNotif'] . ") count=" . $value['SendCount'], 'rsbcron');

                $notification->fullReq = '';
                $notification->httpCode = 0;
                $notification->httpAns = '';

                try {
                    switch ($value['TypeNotif']) {
                        default:
                        case 0:
                            $res = $notification->sendToUser($value);
                            break;
                        case 1:
                            $res = $notification->sendToShop($value);
                            break;
                        case 2:
                            $res = $notification->sendReversHttp($value);
                            break;
                        case 3:
                            // $res = $notification->sendUserReversHttp($value);
                            break;
                    }
                    $connection->createCommand()
                        ->update('notification_pay', [
                            'SendCount' => $value['SendCount'] + 1,
                            'DateLastReq' => time(),
                            'HttpCode' => $notification->httpCode,
                            'HttpAns' => $notification->httpAns,
                            'FullReq' => $notification->fullReq
                        ], '`ID` = :ID', [':ID' => $value['IdNotif']])
                        ->execute();
                    if ($res || $value['SendCount'] > 30) {
                        $connection->createCommand()
                            ->update('notification_pay', [
                                'DateSend' => time()
                            ], '`ID` = :ID', [':ID' => $value['IdNotif']])
                            ->execute();
                    }
                } catch (\Exception $e) {
                    Yii::error("Error Notification ID=" . $value['IdNotif'] . ": " . $e->getMessage(), 'rsbcron');
                }
            }
            unset($value);
        }


    }

    /**
     * Vyvod prinatyh platejeii mfo (1d at 12:30)
     * @throws \yii\db\Exception
     */
    public function actionVyvod()
    {
        echo "Run Vyvod\n";

        $perevod = new VyvodSumPay();
        $perevod->execute();

        if (date('d') == 1) {
            //возмещение космиссии по выдаче 1го числа
            $this->actionReturnComis();
        }
    }

    /**
     * Vyvod voznagrajdenia sistemy (off)
     * @throws \yii\db\Exception
     */
    public function actionVoznag()
    {
        echo "Run Voznag\n";

        $perevod = new VyvodVoznagPlanner();
        $perevod->execute();
    }

    /**
     * Otpravka otchetov v mfo (at 7:00)
     */
    public function actionSendOtch()
    {
        echo "Run Send Otch in csv files. \n";
        $sender = new OtchToEmail(new DistributionReports());
        $sender->run();
        echo "End operation. \n";
    }

    /**
     * Ежедневная автоматическая отправка реестра успешных выплат через банк БРС (ежедневно в 4 утра)
     */
    public function actionSendBrsReport($dateFrom = '', $dateTo = '', $emailList = '')
    {
        echo "Run Send BRS report in csv files. \n";

        $dateFrom = $dateFrom !== '' ? $dateFrom : 'yesterday';
        $dateTo = $dateTo !== '' ? $dateTo : 'today';

        $sender = new BrsReportToEmail(new DistributionReports(), $dateFrom, $dateTo, $emailList);

        try {
            $sender->run();
        } catch (\Exception $e) {
            Yii::$app->errorHandler->logException($e);
            echo "Operation error. \n";
        }

        echo "End operation. \n";
    }

    /**
     * Vozvrat comissii
     * @throws \yii\db\Exception
     */
    public function actionReturnComis()
    {
        echo "Run ReturnComis. \n";

        $ReturnComisMfo = new ReturnComisMfo();
        $ReturnComisMfo->execute();

    }

    /**
     * Obnovlenit vypisok (1h)
     * @throws \yii\db\Exception
     */
    public function actionUpdatestatm()
    {
        echo "Run Updatestatm. \n";

        $Updatestatm = new UpdateStatems();
        $Updatestatm->execute();

        if (VyvodSumPay::PlannerCommonTime()) {
            //в 9:00
            echo "Run VyvodVirt\n";

            $perevod = new VyvodSumPay();
            $perevod->executeVirt();

        }

    }

    /**
     * Proverka sobytii opovescheniii (1m)
     */
    public function actionAlarms()
    {
        if (Yii::$app->params['TESTMODE'] != 'Y') {
            echo "Run Alarms\n";
            $AlertsSend = new AlarmsSend();
            $AlertsSend->execute();
        }
    }

    public function actionVyvodvirt()
    {
        $perevod = new VyvodSumPay();
        $perevod->executeVirt();
    }

    public function actionSendNews()
    {
        if (Yii::$app->params['TESTMODE'] != 'Y') {
            echo "Run SendNews\n";
            $SendNews = new SendNews();
            $SendNews->execute();
        }
    }

    public function actionUpdateTelegram()
    {
        if (Yii::$app->params['TESTMODE'] == 'Y') {
            echo "Run UpdateTelegram\n";

            $Telegram = new Telegram();
            $Telegram->GetMesages();
        }
    }

    public function actionReceiveTelegram()
    {
        echo "Run ReceiveTelegram\n";

        $ReceiveTelegram = new ReceiveTelegram();
        $ReceiveTelegram->execute();
    }

    public function actionSyncBalance($idPartner, $type)
    {
        $partner = Partner::findOne(['ID' => $idPartner]);

        if(!$partner || !in_array($type, ['in', 'out'])) {
            echo "Error!";
            return;
        }

        $table = $type == 'in' ? 'partner_orderin' : 'partner_orderout';
        $field = $type == 'in' ? 'BalanceIn' : 'BalanceOut';

        //при синхронизации учитываем строго транзакции, полученные через импорт банковской выписки
        $q = sprintf("SELECT SUM(Summ) AS Summ FROM %s WHERE IdPartner = %d AND IdStatm <> 0", $table, $idPartner);
        $summ = Yii::$app->db->createCommand($q)->queryScalar();

        $old = $partner[$field];
        $partner[$field] = (int)$summ;
        $partner->save();
        echo sprintf('Old: %d New %d', $old, $summ);
    }

    public function actionCorrectBalance($idPartner, $type, $summ)
    {
        $partner = Partner::findOne(['ID' => $idPartner]);

        if(!$partner || !in_array($type, ['in', 'out'])) {
            echo "Error!";
            return;
        }

        $field = $type == 'in' ? 'BalanceIn' : 'BalanceOut';

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $partner = Partner::findOne(['ID' => $idPartner]);
            $oldBalance = $partner[$field];
            $newBalance = $partner[$field] + $summ;
            $q = sprintf(
                'INSERT INTO partner_orderin (`IdPartner`, `Comment`, `Summ`, `DateOp`, `TypeOrder`, `SummAfter`) VALUES (%d, \'correct\', %d, %d, 0, %d)',
                $idPartner,
                $summ,
                time(),
                $newBalance
            );
            Yii::$app->db->createCommand($q)->execute();

            $partner[$field] = $newBalance;
            $partner->save();
            $transaction->commit();
            echo "Old: $oldBalance New: $newBalance";

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    }

    public function actionPartsBalanceSendToPartners()
    {
        $this->getPaymentService()->sendPartsToPartners();
    }

    public function actionResendNotify($idPartner, $startDate=1604188800, $finishDate=null)
    {
        /** @var NotificationsService $notificationsService */
        $notificationsService = Yii::$container->get('NotificationsService');

        $q = PaySchet::find()
            ->leftJoin('notification_pay', 'notification_pay.IdPay = pay_schet.ID')
            ->where([
                'pay_schet.IdOrg' => $idPartner,
                'notification_pay.ID' => null,
            ])
            ->andWhere(['<>', 'pay_schet.Status', '0'])
            ->andWhere(['>', 'pay_schet.DateCreate', $startDate]);

        if(!is_null($finishDate)) {
            $q->andWhere(['<', 'pay_schet.DateCreate', $finishDate]);
        }

        $page = 0;
        while(true) {
            $paySchets = $q->limit(100)->offset($page * 100)->all();

            if(count($paySchets) == 0) {
                break;
            }

            /** @var PaySchet $paySchet */
            foreach ($paySchets as $paySchet) {
                $paySchet->UserUrlInform = $paySchet->uslugatovar->UrlInform;
                $paySchet->sms_accept = 1;
                $paySchet->save(false);

                echo $paySchet->ID . "\n";

                $notificationsService->addNotificationByPaySchet($paySchet);
                usleep(20);
            }
            unset($paySchet);
            $page++;
        }
    }

    /**
     * Отправка счетов, с поздним обновлением статуса или сообщением
     * (статус обновлен не текущим днем)
     * 9:00
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function actionSendEmailsLateUpdatedPaySchets()
    {
        $this->getPaymentService()->sendEmailsLateUpdatedPaySchets();
    }

    /**
     * @return PaymentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getPaymentService()
    {
        return Yii::$container->get('PaymentService');
    }
}
