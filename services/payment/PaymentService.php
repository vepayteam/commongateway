<?php


namespace app\services\payment;


use app\models\bank\BankCheck;
use app\models\kfapi\KfRequest;
use app\models\partner\stat\export\csv\ToCSV;
use app\models\payonline\Partner;
use app\models\SendEmail;
use app\modules\partner\models\PaySchetLogForm;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\forms\SetPayOkForm;
use app\services\payment\models\PaySchet;
use app\services\payment\models\PaySchetLog;
use app\services\payment\payment_strategies\CreateFormEcomStrategy;
use app\services\payment\payment_strategies\CreateFormJkhStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use app\services\payment\traits\CardsTrait;
use app\services\payment\traits\PayPartsTrait;
use Carbon\Carbon;
use Yii;
use yii\db\Query;
use yii\mutex\FileMutex;

class PaymentService
{
    use PayPartsTrait, CardsTrait;

    public function createPay(KfRequest $kfRequest)
    {
        /** @var IPaymentStrategy $paymentStrategy */
        $paymentStrategy = null;
        switch($kfRequest->GetReq('type', 0)) {
            case 1:
                $paymentStrategy = new CreateFormJkhStrategy($kfRequest);
                break;
            default:
                $paymentStrategy = new CreateFormEcomStrategy($kfRequest);
                break;
        }

        return $paymentStrategy->exec();
    }

    /**
     * @param PaySchet $paySchet
     * @param string $message
     * @return PaySchet
     */
    public function cancelPay(PaySchet $paySchet, $message = '')
    {
        $paySchet->Status = PaySchet::STATUS_ERROR;
        $paySchet->ErrorInfo = mb_substr($message, 0, 250);
        $paySchet->CountSendOK = 0;
        $paySchet->save(false);
        return $paySchet;
    }

    /**
     * @param SetPayOkForm $setPayOkForm
     * @return bool
     */
    public function setPayOK(SetPayOkForm $setPayOkForm)
    {
        if(!$setPayOkForm->validate()) {
            return false;
        }

        $paySchet = $setPayOkForm->paySchet;
        $paySchet->Status = 1;
        $paySchet->PayType = 0;
        $paySchet->TimeElapsed = 1800;
        $paySchet->UserClickPay = 1;
        $paySchet->DateOplat = time();
        $paySchet->ExtBillNumber = $setPayOkForm->paySchet->ExtBillNumber;
        $paySchet->ExtKeyAcces = 0;
        $paySchet->ApprovalCode = $setPayOkForm->approvalCode;
        $paySchet->RRN = $setPayOkForm->rrn;
        $paySchet->RCCode = !empty($setPayOkForm->rcCode) ? mb_substr($setPayOkForm->rcCode, 0, 3) : null;
        $paySchet->ErrorInfo = $setPayOkForm->message;
        $paySchet->CountSendOK = 10;
        $paySchet->save(false);

        return true;
    }

    /**
     * @param PaySchet $paySchet
     * @return bool
     * @throws \yii\db\Exception
     */
    public function setOrderOk(PaySchet $paySchet)
    {
        Yii::$app->db->createCommand()
            ->update('order_pay', [
                'StateOrder' => 1,
                'DateOplata' => time(),
                'IdPaySchet' => $paySchet->ID,
            ], '`ID` = :ID', [':ID' => $paySchet->IdOrder])
            ->execute();
        return true;
    }

    /**
     * @param PaySchetLogForm $paySchetLogForm
     * @return PaySchetLog[]
     */
    public function geyPaySchetLog(PaySchetLogForm $paySchetLogForm)
    {
        $paySchet = $paySchetLogForm->getPaySchet();
        $result = $paySchet->getLog()->orderBy('DateCreate DESC')->all();
        return $result;
    }

    /**
     *
     */
    public function sendEmailsLateUpdatedPaySchets()
    {
        $partnerIds = PaySchetLog::queryLateUpdatedPaySchets()
            ->select('pay_schet.IdOrg')
            ->groupBy('pay_schet.IdOrg')
            ->asArray()
            ->all();

        foreach ($partnerIds as $partnerId) {
            $partner = Partner::findOne(['ID' => $partnerId]);

            $data = PaySchetLog::queryLateUpdatedPaySchets()
                ->select('pay_schet.ExtId, pay_schet_log.PaySchetId, FROM_UNIXTIME(pay_schet_log.DateCreate) AS , pay_schet_log.Status, pay_schet_log.ErrorInfo')
                ->asArray()
                ->all();
            $this->generateAndSendEmailsByPartner($partner, $data);
        }
    }

    /**
     * @param Partner $partner
     * @param array $data
     */
    private function generateAndSendEmailsByPartner(Partner $partner, array $data)
    {
        $today = Carbon::now()->startOfDay();

        $path = Yii::getAlias('@runtime/acts/');
        $filename = sprintf('%d_%d_%d_%d.%s', $partner->ID, $today->day, $today->month, $today->year, 'csv');
        $toCSV = new ToCSV($data, $path, $filename);
        $toCSV->export();

        $sendEmail = new SendEmail();
        $sendEmail->sendReestr(
            $partner->Email,
            'Отчет: платежи с поздним обновлением за ' . $today->day . '.' . $today->month,
            '',
            [[
                'data' => file_get_contents($toCSV->fullpath()),
                'name' => $filename,
            ]]);

        if (file_exists($toCSV->fullpath())){
            unlink($toCSV->fullpath());
        }
    }
}
