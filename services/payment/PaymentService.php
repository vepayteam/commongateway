<?php


namespace app\services\payment;


use app\models\kfapi\KfRequest;
use app\models\partner\stat\export\csv\ToCSV;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\SendEmail;
use app\models\TU;
use app\modules\partner\models\PaySchetLogForm;
use app\services\partners\models\PartnerOption;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\BRSAdapter;
use app\services\payment\banks\IBankAdapter;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\SetPayOkForm;
use app\services\payment\jobs\RecurrentPayJob;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\jobs\RefundPayJob;
use app\services\payment\models\Bank;
use app\services\payment\models\PaySchet;
use app\services\payment\models\PaySchetLog;
use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\CreateFormEcomStrategy;
use app\services\payment\payment_strategies\CreateFormJkhStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use app\services\payment\payment_strategies\mfo\MfoSbpTransferStrategy;
use app\services\payment\traits\CardsTrait;
use app\services\payment\traits\PayPartsTrait;
use app\services\payment\traits\ValidateTrait;
use Carbon\Carbon;
use Yii;

class PaymentService
{
    use PayPartsTrait, CardsTrait, ValidateTrait;

    const GET_SBP_BANK_RECEIVER_CACHE_KEY = 'Getsbpbankreceiver';

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

            $header = [['Ext ID', 'Vepay ID', 'Дата Создания', 'Дата Оплаты', 'Наименование мерчанта', 'Услуга']];
            $data = (new \yii\db\Query)
                ->select(['*'])
                ->from([
                    'pay_schet' => '('
                        . PaySchetLog
                        ::queryLateUpdatedPaySchets($partnerOption->PartnerId, (int)$deltaPartnerOption->Value)
                        ->select(
                             'pay_schet.ID, '
                             . 'pay_schet.ExtId, '
                             . ' pay_schet_log.PaySchetId,'
                             . ' FROM_UNIXTIME(pay_schet.DateCreate) AS DateCreate,'
                             . ' FROM_UNIXTIME(pay_schet.DateOplat) AS DateOplat,'
                             . ' partner.UrLico,'// Наименование мерчанта
                             . ' uslugatovar.NameUsluga,' // Услуга
                        )
                        ->leftJoin('partner', 'pay_schet.IdOrg = partner.ID')
                        ->leftJoin('uslugatovar', 'pay_schet.IdUsluga = uslugatovar.ID')
                        ->andWhere('pay_schet.Status = ' . PaySchet::STATUS_DONE)
                        ->createCommand()
                        ->getRawSql()
                        . ')'
                ])
                ->groupBy('pay_schet.ID')
                ->all();

            if (count($data) > 0) {

                $data = array_merge($header, $data);

                try {
                    $this->generateAndSendEmailsByPartner($partnerOption, $data);
                } catch (\Exception $e) {
                    Yii::warning('sendEmailsLateUpdatedPaySchetsError: ' . $e->getMessage());
                }
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
            Yii::warning('massRefreshStatus add ID=' . $paySchet->ID);
            Yii::$app->queue->push(new RefreshStatusPayJob([
                'paySchetId' => $paySchet->ID,
            ]));
            Yii::warning('PaymentService massRefreshStatus pushed: ID=' . $paySchet->ID);
        }
    }

    /**
     * @param $where
     * @param int $limit
     */
    public function massRepeatExecRecurrent($where, $limit = 0)
    {
        $generator = $this->generatorPaySchetsForWhere($where, $limit);
        /** @var PaySchet $paySchet */
        foreach ($generator as $paySchet) {
            if(!in_array($paySchet->uslugatovar->IsCustom, UslugatovarType::recurrentTypes())) {
                continue;
            }

            Yii::warning('PaymentService massRepeatExecRecurrent add ID=' . $paySchet->ID);
            Yii::$app->queue->push(new RecurrentPayJob([
                'paySchetId' => $paySchet->ID,
            ]));
            Yii::warning('PaymentService massRepeatExecRecurrent pushed: ID=' . $paySchet->ID);

            $paySchet->Status = PaySchet::STATUS_NOT_EXEC;
            $paySchet->ErrorInfo = 'Ожидается обработка';
            $paySchet->save(false);
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

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getSbpBankReceive()
    {
        $data = Yii::$app->cache->getOrSet(self::GET_SBP_BANK_RECEIVER_CACHE_KEY, function() {
            $partner = Partner::findOne(['ID' => Partner::VEPAY_ID]);
            $uslugatovar = Uslugatovar::findOne([
                'IDPartner' => Partner::VEPAY_ID,
                'IsCustom' => TU::$VYVODPAYS,
                'IsDeleted' => 0,
            ]);

            if(!$uslugatovar) {
                throw new \Exception('Услуга не найдена');
            }

            $brsBank = Bank::findOne([
                'ID' => BRSAdapter::$bank,
            ]);

            $bankAdapterBuilder = new BankAdapterBuilder();
            $bankAdapterBuilder->buildByBank($partner, $uslugatovar, $brsBank);

            /** @var BRSAdapter $brsBankAdapter */
            $brsAdapter = $bankAdapterBuilder->getBankAdapter();

            return $brsAdapter->getBankReceiver();
        }, 60 * 60 * 24);
        return $data;
    }

    /**
     * @param OutPayAccountForm $outPayAccountForm
     * @return mixed
     * @throws exceptions\GateException
     */
    public function checkSbpCanTransfer(OutPayAccountForm $outPayAccountForm): TransferToAccountResponse
    {
        /** @var BRSAdapter $brsBankAdapter */
        $brsAdapter = $this->processBankAdapter($outPayAccountForm, BRSAdapter::$bank, TU::$B2CSBP);

        return $brsAdapter->checkTransferB2C($outPayAccountForm);
    }

    /**
     * @param OutPayAccountForm $outPayAccountForm
     * @return PaySchet
     * @throws GateException
     * @throws exceptions\CreatePayException
     */
    public function sbpTransfer(OutPayAccountForm $outPayAccountForm): PaySchet
    {
        $mfoSbpTransferStrategy = new MfoSbpTransferStrategy($outPayAccountForm);
        return $mfoSbpTransferStrategy->exec();
    }

    /**
     * @param PaySchet $paySchet
     * @throws \Exception
     */
    public function doneReversPay(PaySchet $paySchet)
    {
        if($paySchet->Status != PaySchet::STATUS_DONE) {
            throw new \Exception('Можно отменить только успешный платеж');
        }

        $paySchet->Status = PaySchet::STATUS_CANCEL;
        $paySchet->ErrorInfo = 'Возврат платежа';
        $paySchet->CountSendOK = 0;
        $paySchet->save(false);
    }

    /**
     * @param OutPayAccountForm $outPayAccountForm
     * @param int $bankId
     * @param string $uslugatovarType
     *
     * @return IBankAdapter
     * @throws exceptions\GateException
     */
    private function processBankAdapter(OutPayAccountForm $outPayAccountForm, int $bankId, string $uslugatovarType): IBankAdapter
    {
        $uslugatovar = Uslugatovar::findOne([
            'IDPartner' => $outPayAccountForm->partner->ID,
            'IsCustom' => $uslugatovarType,
            'IsDeleted' => 0,
        ]);
        $bank = Bank::findOne([
            'ID' => $bankId,
        ]);

        if (!$uslugatovar || !$bank) {
            throw new GateException("Нет шлюза. partnerId=".$outPayAccountForm->partner->ID.", uslugatovarType=$uslugatovarType bankId=$bankId");
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($outPayAccountForm->partner, $uslugatovar, $bank);

        return $bankAdapterBuilder->getBankAdapter();
    }
}
