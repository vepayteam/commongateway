<?php

namespace app\commands;

use app\models\bank\TCBank;
use app\models\bank\WithdrawTCBankIterable;
use app\models\mfo\DistributionReports;
use app\models\payonline\Partner;
use app\models\Payschets;
use app\models\planner\AlarmsSend;
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
use Yii;
use yii\console\Controller;
use app\models\payonline\OrderNotif;
use app\models\planner\CheckpayCron;
use app\models\planner\Notification;
use yii\db\Transaction;
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

        /*if (date('G') == 0) {
            //ocm комиссия 1.5%
            Yii::$app->db->createCommand()->update('uslugatovar', [
                'MinsumComiss' => 0
            ],'IDPartner = 8 AND IsCustom IN (10,14)')->execute();
        }*/
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
        if ($notification->needReversOrderIds()){ //если массив пустой то пропускаем этап возврата средств.
            $withdraw =  new WithdrawTCBankIterable(
                $notification->needReversOrderIds(),
                new Payschets()
            );
            $withdraw->start();
        }
        $ordernotif = new OrderNotif();
        $ordernotif->SendEmails();

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

        $q = sprintf("SELECT SUM(Summ) AS Summ FROM %s WHERE IdPartner = %d", $table, $idPartner);
        $summ = Yii::$app->db->createCommand($q)->queryScalar();

        $old = $partner[$field];
        $partner[$field] = $summ;
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

        $paySchets = PaySchet::find()
            ->where([
                'IdOrg' => $idPartner,
            ])
            ->andWhere(['is', 'UserUrlInform', null])
            ->andWhere(['>', 'DateCreate', $startDate]);

        if(!is_null($finishDate)) {
            $paySchets->andWhere(['<', 'DateCreate', $finishDate]);
        }

        /** @var PaySchet $paySchet */
        foreach ($paySchets->each() as $paySchet) {
            $paySchet->UserUrlInform = $paySchet->uslugatovar->UrlInform;
            $paySchet->sms_accept = 1;
            $paySchet->save(false);

            $notificationsService->addNotificationByPaySchet($paySchet);
            Yii::warning('actionResendNotify: ' . $paySchet->ID, 'merchant');
        }
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
