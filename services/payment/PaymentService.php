<?php


namespace app\services\payment;


use app\models\kfapi\KfRequest;
use app\models\partner\stat\export\csv\ToCSV;
use app\models\SendEmail;
use app\modules\partner\models\PaySchetLogForm;
use app\services\partners\models\PartnerOption;
use app\services\payment\forms\SetPayOkForm;
use app\services\payment\jobs\RefundPayJob;
use app\services\payment\models\PaySchet;
use app\services\payment\models\PaySchetLog;
use app\services\payment\payment_strategies\CreateFormEcomStrategy;
use app\services\payment\payment_strategies\CreateFormJkhStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\traits\CardsTrait;
use app\services\payment\traits\PayPartsTrait;
use Carbon\Carbon;
use Yii;

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

            $data = PaySchetLog::queryLateUpdatedPaySchets($partnerOption->PartnerId, (int)$deltaPartnerOption->Value)
                ->select(
                    'pay_schet.ExtId, '
                    . ' pay_schet_log.PaySchetId,'
                    . ' FROM_UNIXTIME(pay_schet.DateCreate) AS DateCreate,'
                    . ' FROM_UNIXTIME(pay_schet.DateOplat) AS DateOplat,'
                    . ' partner.UrLico,'// Наименование мерчанта
                    . ' uslugatovar.NameUsluga,'// Услуга
                    . ' pay_schet_log.Status')
                ->leftJoin('partner', 'pay_schet.IdOrg = partner.ID')
                ->leftJoin('uslugatovar', 'pay_schet.IdUsluga = uslugatovar.ID')
                ->andWhere('pay_schet.Status = ' . PaySchet::STATUS_DONE)
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

    /**
     * @param $where
     * @param int $limit
     */
    public function massRevert($where, $limit = 0)
    {
        $generator = $this->generatorPaySchetsForWhere($where, $limit);

        foreach ($generator as $paySchet) {
            Yii::$app->queue->push(new RefundPayJob([
                'paySchetId' => $paySchet->ID,
            ]));
            Yii::warning('PaymentService massRevert pushed: ID=' . $paySchet->ID);
        }
    }

    /**
     * @param $where
     * @param int $limit
     */
    public function massRefreshStatus($where, $limit = 0)
    {
        $generator = $this->generatorPaySchetsForWhere($where, $limit);

        foreach ($generator as $paySchet) {
            Yii::warning('massRefreshStatus add ID=' . $paySchet->ID, 'RefreshStatusPayJob');
            Yii::$app->queue->push(new RefreshStatusPayJob([
                'paySchetId' => $paySchet->ID,
            ]));
            Yii::warning('PaymentService massRefreshStatus pushed: ID=' . $paySchet->ID);
        }
    }

    /**
     * @param $where
     * @param $limit
     * @return \Generator
     */
    private function generatorPaySchetsForWhere($where, $limit)
    {
        $perPage = 100;
        $page = 0;

        while(true) {
            if($limit > 0 && $page * $perPage > $limit) {
                break;
            }

            $q = $paySchets = PaySchet::find()->where($where)->offset($page * $perPage);

            if($limit > 0 && $limit - $page * $perPage < $perPage) {
                $q->limit($limit - $page * $perPage);
            } else {
                $q->limit($perPage);
            }

            /** @var PaySchet[] $paySchets */
            $paySchets = $q->all();

            if(count($paySchets) == 0) {
                break;
            }

            foreach($paySchets as $paySchet) {
                yield $paySchet;
            }
            $page++;
        }
    }
}
