<?php

namespace app\commands;

use app\models\bank\TCBank;
use app\models\bank\WithdrawTCBankIterable;
use app\models\mfo\DistributionReports;
use app\models\Payschets;
use app\models\planner\AlarmsSend;
use app\models\planner\OtchToEmail;
use app\models\planner\ReturnComisMfo;
use app\models\planner\UpdateStatems;
use app\models\planner\VyvodSumPay;
use app\models\planner\VyvodVoznagPlanner;
use Yii;
use yii\console\Controller;
use app\models\payonline\OrderNotif;
use app\models\planner\CheckpayCron;
use app\models\planner\Notification;
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

        if (date('G') == 0) {
            //ocm комиссия 1.5%
            Yii::$app->db->createCommand()->update('uslugatovar', [
                'MinsumComiss' => 0
            ],'IDPartner = 8 AND IsCustom IN (10,14)')->execute();
        }
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
     * Vyvod prinatyh platejeii mfo (1d)
     * @throws \yii\db\Exception
     */
    public function actionVyvod()
    {
        echo "Run Vyvod\n";

        $perevod = new VyvodSumPay();
        $perevod->execute();
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
     * Vozvrat comissii (off)
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
        echo "Run Alarms\n";
        $AlertsSend = new AlarmsSend();
        $AlertsSend->execute();

    }

    public function actionVyvodvirt()
    {
        $perevod = new VyvodSumPay();
        $perevod->executeVirt();
    }


}