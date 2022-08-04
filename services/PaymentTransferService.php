<?php

namespace app\services;

use app\models\mfo\VyvodReestr;
use app\models\mfo\VyvodSystem;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\models\Bank;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\paymentTransfer\PartnerRequisites;
use app\services\paymentTransfer\PaymentTransferException;
use app\services\paymentTransfer\TransferRewardForm;
use app\services\paymentTransfer\TransferFundsForm;
use Carbon\Carbon;
use yii\base\Component;

class PaymentTransferService extends Component
{
    private const PARTNER_ID_VEPAY = 1;

    /**
     * @param TransferRewardForm $form
     * @return void
     * @throws PaymentTransferException
     */
    public function transferReward(TransferRewardForm $form)
    {
        $partner = Partner::findOne(['ID' => $form->getPartner()]);

        $uslugatovar = $this->getUslugatovar($partner, UslugatovarType::VYPLATVOZN);
        if ($uslugatovar === null) {
            \Yii::error([
                'message' => 'Uslugatovar not found for partner',
                'partnerId' => $partner->ID,
            ]);
            throw new PaymentTransferException('Uslugatovar not found for partner');
        }

        $bankAdapterBuilder = new BankAdapterBuilder();

        try {
            $bankAdapterBuilder->build($partner, $uslugatovar);
        } catch (GateException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new PaymentTransferException('Gate not found');
        }

        $requisites = $this->getRequisites(Partner::findOne(['ID' => self::PARTNER_ID_VEPAY]));
        $description = sprintf(
            "Вознаграждение за оказанные услуги по договору %s от %s, за %s-%s",
            $partner->NumDogovor,
            $partner->DateDogovor,
            $form->getDateFrom()->format('d.m.Y'),
            $form->getDateTo()->format('d.m.Y')
        );

        $vyvodSystem = new VyvodSystem();
        $vyvodSystem->DateOp = time();
        $vyvodSystem->IdPartner = $partner->ID;
        $vyvodSystem->DateFrom = $form->getDateFrom()->timestamp;
        $vyvodSystem->DateTo = $form->getDateTo()->timestamp;
        $vyvodSystem->Summ = $form->getSum();
        $vyvodSystem->SatateOp = VyvodSystem::STATE_CREATED;
        $vyvodSystem->IdPay = 0;
        $vyvodSystem->TypeVyvod = $form->getType();
        $vyvodSystem->save();

        if ($form->getType() === TransferRewardForm::TYPE_STANDARD) {
            $paySchet = $this->createPaySchet(
                $uslugatovar,
                $partner,
                $bankAdapterBuilder,
                $requisites,
                $description,
                $form->getSum(),
                'voznout.' . $vyvodSystem->ID
            );

            $transferToAccountResponse = $this->processPayment(
                $paySchet,
                $partner,
                $bankAdapterBuilder,
                $requisites,
                $description,
                $form->getSum()
            );

            $vyvodSystem->SatateOp = $transferToAccountResponse->status === BaseResponse::STATUS_DONE
                ? VyvodSystem::STATE_SUCCESS
                : VyvodSystem::STATE_ERROR;
            $vyvodSystem->IdPay = $paySchet->ID;
            $vyvodSystem->save();
        } else {
            $vyvodSystem->SatateOp = VyvodSystem::STATE_SUCCESS;
            $vyvodSystem->save();
        }
    }

    /**
     * @param TransferFundsForm $form
     * @return void
     * @throws PaymentTransferException
     */
    public function transferFunds(TransferFundsForm $form)
    {
        $paymentAmount = PaymentHelper::convertToPenny($form->getSum());
        $dateFrom = Carbon::yesterday();
        $dateTo = Carbon::yesterday()->endOfDay();

        $partner = Partner::findOne(['ID' => $form->getIdPartner()]);
        $bank = Bank::findOne(['ID' => $form->getBankId()]);

        if ($form->getTypeSchet() === TransferFundsForm::TYPE_INTERNAL) {
            \Yii::error([
                'message' => 'Internal transfer between accounts is deprecated',
                'partnerId' => $partner->ID,
            ]);
            throw new PaymentTransferException('Internal transfer between accounts is deprecated');
        }

        $uslugatovar = $this->getUslugatovar($partner, UslugatovarType::VYVODPAYS);
        if ($uslugatovar === null) {
            \Yii::error([
                'message' => 'Uslugatovar not found for partner',
                'partnerId' => $partner->ID,
            ]);
            throw new PaymentTransferException('Uslugatovar not found for partner');
        }

        $bankAdapterBuilder = new BankAdapterBuilder();

        try {
            $bankAdapterBuilder->buildByBank($partner, $uslugatovar, $bank);
        } catch (GateException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new PaymentTransferException('Gate not found');
        }

        $requisites = $this->getRequisites($partner);
        $description = sprintf(
            "Расчеты по договору %s от %s согласно реестру за %s г.",
            $partner->NumDogovor,
            $partner->DateDogovor,
            $dateFrom->format('d.m.Y')
        );

        $vyvodReestr = new VyvodReestr();
        $vyvodReestr->IdPartner = $partner->ID;
        $vyvodReestr->DateFrom = $dateFrom->timestamp;
        $vyvodReestr->DateTo = $dateTo->timestamp;
        $vyvodReestr->SumOp = $paymentAmount;
        $vyvodReestr->StateOp = VyvodReestr::STATE_CREATED;
        $vyvodReestr->IdPay = 0;
        $vyvodReestr->TypePerechisl = $form->getTypeSchet();
        $vyvodReestr->save();

        $paySchet = $this->createPaySchet(
            $uslugatovar,
            $partner,
            $bankAdapterBuilder,
            $requisites,
            $description,
            $paymentAmount,
            'reestr.' . $vyvodReestr->ID
        );

        $transferToAccountResponse = $this->processPayment(
            $paySchet,
            $partner,
            $bankAdapterBuilder,
            $requisites,
            $description,
            $paymentAmount
        );

        $vyvodReestr->StateOp = $transferToAccountResponse->status === BaseResponse::STATUS_DONE
            ? VyvodReestr::STATE_SUCCESS
            : VyvodReestr::STATE_ERROR;
        $vyvodReestr->IdPay = $paySchet->ID;
        $vyvodReestr->save();
    }

    /**
     * @return array
     */
    public function getLegacyRewardRequisites(): array
    {
        $partner = Partner::findOne(['ID' => self::PARTNER_ID_VEPAY]);
        $requisites = $this->getRequisites($partner);

        return [
            'bic' => $requisites->bic,
            'inn' => $requisites->inn,
            'kpp' => $requisites->kpp,
            'account' => $requisites->settlementAccount,
            'ks' => $requisites->correspondentAccount,
            'name' => $requisites->recipientName,
            'bankname' => $requisites->bankName,
            'bankcity' => $requisites->bankCity,
        ];
    }

    /**
     * @param Partner $partner
     * @return PartnerRequisites
     */
    private function getRequisites(Partner $partner): PartnerRequisites
    {
        $rekviz = $partner->bankRekviz;

        $requisites = new PartnerRequisites();
        $requisites->bic = $rekviz->BIKPoluchat;
        $requisites->inn = $rekviz->INNPolushat;
        $requisites->kpp = $rekviz->KPPPoluchat;
        $requisites->settlementAccount = $rekviz->RaschShetPolushat;
        $requisites->correspondentAccount = $rekviz->KorShetPolushat;
        $requisites->recipientName = $rekviz->NamePoluchat;
        $requisites->bankName = $rekviz->NameBankPoluchat;
        $requisites->bankCity = $rekviz->SityBankPoluchat;

        return $requisites;
    }

    /**
     * @param PaySchet $paySchet
     * @param Partner $partner
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @param PartnerRequisites $requisites
     * @param string $description
     * @param int $paymentAmount
     * @return TransferToAccountResponse
     */
    private function processPayment(
        PaySchet           $paySchet,
        Partner            $partner,
        BankAdapterBuilder $bankAdapterBuilder,
        PartnerRequisites  $requisites,
        string             $description,
        int                $paymentAmount
    ): TransferToAccountResponse
    {
        $outPayAccountForm = new OutPayAccountForm();
        $outPayAccountForm->paySchet = $paySchet;
        $outPayAccountForm->partner = $partner;
        $outPayAccountForm->amount = $paymentAmount;
        $outPayAccountForm->descript = $description;
        $outPayAccountForm->account = $requisites->settlementAccount;
        $outPayAccountForm->bic = $requisites->bic;
        $outPayAccountForm->inn = $requisites->inn;
        $outPayAccountForm->name = $requisites->recipientName;

        $transferToAccountResponse = $bankAdapterBuilder
            ->getBankAdapter()
            ->transferToAccount($outPayAccountForm);

        if ($transferToAccountResponse->status === PaySchet::STATUS_DONE) {
            $paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
            $paySchet->ErrorInfo = $transferToAccountResponse->message;
            $paySchet->ExtBillNumber = $transferToAccountResponse->trans;
            $paySchet->UrlFormPay = '';
            $paySchet->save(false);

            \Yii::$app->queue->push(new RefreshStatusPayJob([
                'paySchetId' => $paySchet->ID,
            ]));
        } else {
            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->ErrorInfo = $transferToAccountResponse->message;
            $paySchet->save(false);
        }

        return $transferToAccountResponse;
    }

    /**
     * @param Uslugatovar $uslugatovar
     * @param Partner $partner
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @param PartnerRequisites $requisites
     * @param string $description
     * @param int $paymentAmount
     * @param string $extId
     * @return PaySchet
     */
    private function createPaySchet(
        Uslugatovar        $uslugatovar,
        Partner            $partner,
        BankAdapterBuilder $bankAdapterBuilder,
        PartnerRequisites  $requisites,
        string             $description,
        int                $paymentAmount,
        string             $extId
    ): PaySchet
    {
        $params = [
            $requisites->settlementAccount,
            $requisites->bic,
            $requisites->recipientName,
            $requisites->inn,
            $requisites->kpp,
            $description,
        ];

        $paySchet = new PaySchet();
        $paySchet->IdUsluga = $uslugatovar->ID;
        $paySchet->IdOrg = $partner->ID;
        $paySchet->Extid = $extId;
        $paySchet->QrParams = implode("|", $params); // TODO check whether this field needs to be filled in
        $paySchet->SummPay = $paymentAmount;
        $paySchet->Status = PaySchet::STATUS_WAITING;
        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->TypeWidget = 3;
        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->sms_accept = 1;
        $paySchet->save(false);

        return $paySchet;
    }

    /**
     * @param Partner $partner
     * @param int $uslugatovarType
     * @return Uslugatovar|null
     */
    private function getUslugatovar(Partner $partner, int $uslugatovarType): ?Uslugatovar
    {
        return Uslugatovar::find()
            ->andWhere([
                'IDPartner' => $partner->ID,
                'IsCustom' => $uslugatovarType,
                'IsDeleted' => 0,
            ])->one();
    }
}