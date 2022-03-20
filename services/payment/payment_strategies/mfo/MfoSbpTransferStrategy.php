<?php

namespace app\services\payment\payment_strategies\mfo;

use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\NotUniquePayException;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\models\PaySchet;
use Yii;

class MfoSbpTransferStrategy
{
    /** @var OutPayAccountForm */
    protected $outPayaccForm;
    /** @var TransferToAccountResponse */
    public $transferToAccountResponse;

    /**
     * @param OutPayAccountForm $outPayaccForm
     */
    public function __construct(OutPayAccountForm $outPayaccForm)
    {
        $this->outPayaccForm = $outPayaccForm;
    }

    /**
     * @return PaySchet
     * @throws CreatePayException
     * @throws GateException
     * @throws NotUniquePayException
     */
    public function exec()
    {
        $uslugatovar = $this->getUslugatovar();
        if(!$uslugatovar) {
            throw new GateException('uslugatovar not found.');
        }
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->outPayaccForm->partner, $uslugatovar);

        if($this->outPayaccForm->extid) {
            $replyPaySchet = $this->getReplyPaySchet();

            if($replyPaySchet) {
                return $replyPaySchet;
            }
        }

        $this->outPayaccForm->paySchet = $this->createPaySchet($bankAdapterBuilder);
        $this->transferToAccountResponse = $bankAdapterBuilder->getBankAdapter()->transferB2C($this->outPayaccForm);

        if($this->transferToAccountResponse->status == BaseResponse::STATUS_DONE) {
            $this->outPayaccForm->paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
            $this->outPayaccForm->paySchet->ExtBillNumber = $this->transferToAccountResponse->trans;
            $this->outPayaccForm->paySchet->ErrorInfo = 'Ожидает запрос статуса';

            Yii::$app->queue->push(new RefreshStatusPayJob([
                'paySchetId' => $this->outPayaccForm->paySchet->ID,
            ]));
        } else {
            $this->outPayaccForm->paySchet->Status = PaySchet::STATUS_ERROR;
            $this->outPayaccForm->paySchet->ErrorInfo = $this->transferToAccountResponse->message;
        }

        $this->outPayaccForm->paySchet->save(false);
        return $this->outPayaccForm->paySchet;
    }

    /**
     * @return PaySchet|null
     * @throws NotUniquePayException
     */
    protected function getReplyPaySchet()
    {
        $paySchet = PaySchet::findOne([
            'Extid' => $this->outPayaccForm->extid,
            'IdOrg' => $this->outPayaccForm->partner->ID,
        ]);

        if($paySchet) {
            throw new NotUniquePayException($paySchet->ID, $paySchet->Extid);
        } else {
            return null;
        }
    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     *
     * @return PaySchet
     * @throws CreatePayException
     */
    protected function createPaySchet(BankAdapterBuilder $bankAdapterBuilder)
    {
        $paySchet = new PaySchet();
        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $bankAdapterBuilder->getUslugatovar()->ID;
        $paySchet->IdOrg = $this->outPayaccForm->partner->ID;
        $paySchet->Extid = $this->outPayaccForm->extid;
        $paySchet->QrParams = $this->outPayaccForm->descript;
        $paySchet->SummPay = $this->outPayaccForm->amount;
        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->IsAutoPay = 0;
        $paySchet->UserUrlInform = $bankAdapterBuilder->getUslugatovar()->UrlInform;
        $paySchet->sms_accept = 1;

        if (!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }

        return $paySchet;
    }

    /**
     * @return Uslugatovar|null
     */
    protected function getUslugatovar()
    {
        return $this
            ->outPayaccForm
            ->partner
            ->getUslugatovars()
            ->where([
                'IsCustom' => TU::$B2CSBP,
                'IsDeleted' => 0,
            ])
            ->one();
    }

}
