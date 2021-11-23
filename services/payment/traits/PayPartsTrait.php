<?php


namespace app\services\payment\traits;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\partner\admin\VyvodParts;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\payonline\PartnerBankRekviz;
use app\models\payonline\Provparams;
use app\models\payonline\Uslugatovar;
use app\models\PayschetPart;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\forms\CreatePartsOutPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\models\PaySchet;
use app\services\PaySchetService;
use Carbon\Carbon;
use Yii;
use yii\db\Query;

trait PayPartsTrait
{
    /**
     *
     */
    public function sendPartsToPartners()
    {
        $dateFrom = Carbon::now()->addDays(-1)->startOfDay();
        $dateTo = Carbon::now()->startOfDay();

        $senderPartners = $this->getPartsSenderPartners($dateFrom, $dateTo);

        /** @var Partner $senderPartner */
        foreach ($senderPartners as $senderPartner) {
            $recipientPartners = $this->getPartsRecipientPartners($senderPartner, $dateFrom, $dateTo);

            /** @var Partner $recipientPartner */
            foreach ($recipientPartners as $recipientPartner) {
                $this->payPartsSenderToRecipient($senderPartner, $recipientPartner, $dateFrom, $dateTo);
            }
        }
    }

    /**
     * @param Partner $senderPartner
     * @param Partner $recipientPartner
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return boolean
     * @throws \yii\db\Exception
     */
    private function payPartsSenderToRecipient(Partner $senderPartner, Partner $recipientPartner, Carbon $dateFrom, Carbon $dateTo)
    {
        $transaction = Yii::$app->db->beginTransaction();

        $transactionOk = true;
        try {
            $vyvodParts = new VyvodParts();
            $vyvodParts->SenderId = $senderPartner->ID;
            $vyvodParts->RecipientId = $recipientPartner->ID;
            $vyvodParts->PayschetId = 0;
            $vyvodParts->Amount = 0;
            $vyvodParts->DateCreate = Carbon::now()->timestamp;
            $vyvodParts->Status = VyvodParts::STATUS_CREATED;
            $transactionOk &= $vyvodParts->save(false);

            $data = PayschetPart::find()
                ->innerJoin('pay_schet', 'pay_schet.ID = pay_schet_parts.PayschetId')
                ->where([
                    'pay_schet_parts.VyvodId' => 0,
                    'pay_schet.Status' => 1,
                    'pay_schet_parts.PartnerId' => $recipientPartner->ID,
                    'pay_schet.IdOrg' => $senderPartner->ID,

                ])
                ->andWhere(['=', 'pay_schet.IdOrg', $senderPartner->ID])
                ->andWhere(['>=', 'pay_schet.DateCreate', $dateFrom->timestamp])
                ->andWhere(['<=', 'pay_schet.DateCreate', $dateTo->timestamp])
                ->all();

            /** @var PayschetPart $row */
            foreach ($data as $payschetPart) {
                $vyvodParts->Amount += $payschetPart->Amount;
            }

            $uslugatovar = Uslugatovar::findOne([
                'IDPartner' => $senderPartner->ID,
                'IsCustom' => TU::$VYVODPAYSPARTS,
            ]);

            if(!$uslugatovar) {
                Yii::warning("VyvodParts: error mfo=" . $senderPartner->ID . " У получателя нет услуги перечисления разбивки ", 'pay-parts');
                $transaction->rollBack();
                return false;
            }

            // TODO: multibank
            $descript = sprintf(
                'Перечисление сумм разбивок для %s (%d) за %s',
                $recipientPartner->Name,
                $recipientPartner->ID,
                $dateFrom->locale('ru')->format('d-m-Y')
            );

            $bankAdapterBuilder = new BankAdapterBuilder();
            $bankAdapterBuilder->build($senderPartner, $uslugatovar);

            $bankAdapter = $bankAdapterBuilder->getBankAdapter();
            $createPartsOutPayForm = new CreatePartsOutPayForm();
            $createPartsOutPayForm->partnerBankGate = $bankAdapterBuilder->getPartnerBankGate();
            $createPartsOutPayForm->uslugatovar = $uslugatovar;
            $createPartsOutPayForm->amount = $vyvodParts->Amount;
            $createPartsOutPayForm->partnerBankRekviz = $recipientPartner->partner_bank_rekviz[0];

            $paySchet = $this->createPartsOutPay($createPartsOutPayForm);

            $outPayAccountForm = new OutPayAccountForm();
            $outPayAccountForm->scenario = OutPayAccountForm::SCENARIO_UL;
            $outPayAccountForm->paySchet = $paySchet;
            $outPayAccountForm->partner = $senderPartner;
            $outPayAccountForm->extid = '';
            $outPayAccountForm->name = $recipientPartner->partner_bank_rekviz[0]->NamePoluchat;
            $outPayAccountForm->account = $recipientPartner->partner_bank_rekviz[0]->RaschShetPolushat;
            $outPayAccountForm->bic = $recipientPartner->partner_bank_rekviz[0]->BIKPoluchat;
            $outPayAccountForm->descript = $descript;
            $outPayAccountForm->amount = $vyvodParts->Amount;

            $transferToAccountResponse = $bankAdapter->transferToAccount($outPayAccountForm);
            if($transferToAccountResponse->status == BaseResponse::STATUS_DONE) {
                $paySchet->ExtBillNumber = $transferToAccountResponse->trans;
                $paySchet->save(false);

                $vyvodParts->PayschetId = $paySchet->ID;
                $vyvodParts->Status = VyvodParts::STATUS_COMPLETED;
            } else {
                $vyvodParts->Status = VyvodParts::STATUS_ERROR;
            }
            $vyvodParts->save();

            /** @var PayschetPart $row */
            foreach ($data as $payschetPart) {
                $payschetPart->VyvodId = $vyvodParts->Id;
                $transactionOk &= $payschetPart->save(false);
            }

            if($transactionOk) {
                $transaction->commit();
            } else {
                $transaction->rollBack();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return Partner[]
     */
    private function getPartsSenderPartners(Carbon $dateFrom, Carbon $dateTo)
    {
        $query = new Query();
        $partnerSenderIds = $query
            ->select([
                'p.IdOrg',
            ])
            ->from('pay_schet_parts AS pp')
            ->innerJoin('pay_schet AS p', 'p.ID = pp.PayschetId')
            ->where([
                'pp.VyvodId' => 0,
                'p.Status' => 1,
            ])
            ->andWhere(['>=', 'p.DateCreate', $dateFrom->timestamp])
            ->andWhere(['<=', 'p.DateCreate', $dateTo->timestamp])
            ->groupBy('p.IdOrg')
            ->column();

        $result = Partner::find()->where(['in', 'ID', $partnerSenderIds])->all();
        return $result;
    }

    /**
     * @param $partnerId
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return Partner[]
     */
    private function getPartsRecipientPartners($partner, Carbon $dateFrom, Carbon $dateTo)
    {
        $query = new Query();
        $partnerRecipientIds = $query
            ->select([
                'pp.PartnerId',
            ])
            ->from('pay_schet_parts as pp')
            ->innerJoin('pay_schet as p', 'p.ID = pp.PayschetId')
            ->where([
                'pp.VyvodId' => 0,
                'p.Status' => 1,
                'p.IdOrg' => $partner->ID,
            ])
            ->andWhere(['>=', 'p.DateCreate', $dateFrom->timestamp])
            ->andWhere(['<=', 'p.DateCreate', $dateTo->timestamp])
            ->groupBy('pp.PartnerId')
            ->column();

        $result = Partner::find()->where(['in', 'ID', $partnerRecipientIds])->all();
        return $result;
    }

    /**
     * @param CreatePartsOutPayForm $createPartsOutPayForm
     * @return PaySchet
     * @throws CreatePayException
     */
    protected function createPartsOutPay(CreatePartsOutPayForm $createPartsOutPayForm)
    {
        $paySchet = new PaySchet();

        $paySchet->IdUsluga       = $createPartsOutPayForm->uslugatovar->ID;
        $paySchet->IdUser         = 0;
        $paySchet->SummPay        = $createPartsOutPayForm->amount;
        $paySchet->UserClickPay   = 0;
        $paySchet->DateCreate     = time();
        $paySchet->Status         = 0;
        $paySchet->DateOplat      = 0;
        $paySchet->DateLastUpdate = time();
        $paySchet->PayType        = 0;
        $paySchet->TimeElapsed    = 86400;
        $paySchet->ExtKeyAcces    = 0;
        $paySchet->CountSendOK    = 0;
        $paySchet->Period         = 0;

        $paySchet->Schetcheks     = '';
        $paySchet->IdAgent        = 0;
        $paySchet->IsAutoPay      = 0;
        $paySchet->AutoPayIdGate  = 0;
        $paySchet->TypeWidget     = 0;
        $paySchet->Bank           = $createPartsOutPayForm->partnerBankGate->BankId;
        $paySchet->IdOrg          = $createPartsOutPayForm->partnerBankGate->PartnerId;
        $paySchet->sms_accept     = 1;

        if(!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }

        return $paySchet;
    }

}
