<?php


namespace app\services\payment;


use app\models\bank\BankCheck;
use app\models\kfapi\KfRequest;
use app\models\partner\stat\export\csv\ToCSV;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\SendEmail;
use app\modules\partner\models\PaySchetLogForm;
use app\services\partners\models\PartnerOption;
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
        $partnerOptions = PartnerOption::find()
            ->where(['Name' => PartnerOption::EMAILS_BY_SEND_LATE_UPDATE_PAY_SCHETS_NAME])
            ->all();

        foreach ($partnerOptions as $partnerOption) {
            if(empty($partnerOption->Value)) {
                continue;
            }

            $deltaPartnerOption = PartnerOption::find()
                ->where([
                    'PartnerId' => $partnerOption->PartnerId,
                    'Name' => PartnerOption::DELTA_TIME_LATE_UPDATE_PAY_SCHETS_NAME,
                ])
                ->one();

            $data = PaySchetLog::queryLateUpdatedPaySchets((int)$deltaPartnerOption->Value)
                ->select('pay_schet.ExtId, pay_schet_log.PaySchetId, FROM_UNIXTIME(pay_schet_log.DateCreate) AS DateCreate, pay_schet_log.Status, pay_schet_log.ErrorInfo')
                ->asArray()
                ->all();

            try {
                $this->generateAndSendEmailsByPartner($partnerOption, $data);
            } catch (\Exception $e) {
                Yii::warning('sendEmailsLateUpdatedPaySchetsError: ' . $e->getMessage());
            }
        }
    }

    /**
     * @param PartnerOption $partnerOption
     * @param array $data
     */
    private function generateAndSendEmailsByPartner(PartnerOption $partnerOption, array $data)
    {
        $today = Carbon::now()->startOfDay();

        $path = Yii::getAlias('@runtime/acts/');
        $filename = sprintf('%d_%d_%d_%d.%s', $partnerOption->PartnerId, $today->day, $today->month, $today->year, 'csv');
        $toCSV = new ToCSV($data, $path, $filename);
        $toCSV->export();

        foreach (explode(',', $partnerOption->Value) as $email) {
            $sendEmail = new SendEmail();
            $sendEmail->sendReestr(
                $email,
                'Отчет: платежи с поздним обновлением за ' . $today->day . '.' . $today->month,
                '',
                [[
                    'data' => file_get_contents($toCSV->fullpath()),
                    'name' => $filename,
                ]]);
        }
        if (file_exists($toCSV->fullpath())){
            unlink($toCSV->fullpath());
        }
    }

    public function refreshNotRefundRegistrationCard(Carbon $startDate, Carbon $finishDate, $limit = 0)
    {
        $perPage = 100;
        $page = 0;

        while(true) {
            if($limit > 0 && $page * $perPage > $limit) {
                break;
            }

            $q = $paySchets = PaySchet::find()
                ->andWhere(['=', 'IdUsluga', Uslugatovar::TYPE_REG_CARD])
                ->andWhere(['=', 'Status', PaySchet::STATUS_DONE])
                ->andWhere(['>', 'DateCreate', $startDate->timestamp])
                ->andWhere(['<', 'DateCreate', $finishDate->timestamp])
                ->offset($page * $perPage);


            if($limit - $page * $perPage < $perPage) {
                $q->limit($limit - $page * $perPage);
            } else {

            }


        }
    }
}
