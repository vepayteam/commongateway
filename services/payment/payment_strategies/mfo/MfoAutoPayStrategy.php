<?php


namespace app\services\payment\payment_strategies\mfo;


use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\Uslugatovar;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use yii\base\Exception;
use yii\mutex\FileMutex;

class MfoAutoPayStrategy
{
    /** @var AutoPayForm */
    protected $autoPayForm;

    /**
     * MfoAutoPayStrategy constructor.
     * @param AutoPayForm $autoPayForm
     */
    public function __construct(AutoPayForm $autoPayForm)
    {
        $this->autoPayForm = $autoPayForm;
    }

    /**
     * @return PaySchet
     * @throws CreatePayException
     * @throws GateException
     */
    public function exec()
    {
        /** @var Uslugatovar $uslugatovar */
        $uslugatovar = $this->getUslugatovar();

        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->autoPayForm->partner, $uslugatovar);

        $mutexKey = 'autoPay' . $this->autoPayForm->partner->ID . $this->autoPayForm->extid;
        $mutex = new FileMutex();

        $replyPaySchet = $this->getReplyRequest($bankAdapterBuilder);
        if($replyPaySchet && $replyPaySchet->SummPay == $this->autoPayForm->amount) {
            return $replyPaySchet;
        } elseif ($replyPaySchet && $replyPaySchet->SummPay != $this->autoPayForm->amount) {
            $mutex->release($mutexKey);
            throw new CreatePayException('Нарушение уникальности запроса');
        }

        if (!$mutex->acquire($mutexKey, 30)) {
            throw new CreatePayException('getPaySchetExt: error lock!');
        }

        $card = $this->autoPayForm->getCard();
        $cardnum = null;
        if ($this->autoPayForm->getCard()->IdPan > 0) {
            $CardToken = new CardToken();
            $cardnum = $CardToken->GetCardByToken($card->IdPan);
        }
        
        if(!$cardnum) {
            $mutex->release($mutexKey);
            throw new CreatePayException('empty card');
        }
        $this->autoPayForm->getCard()->CardNumber = $cardnum;

        $paySchet = $this->createPaySchet($bankAdapterBuilder, $card);
        $this->autoPayForm->paySchet = $paySchet;

        try {
            $createRecurrentPayResponse = $bankAdapterBuilder->getBankAdapter()->recurrentPay($this->autoPayForm);
        } catch (GateException $e) {
            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->ErrorInfo = $e->getMessage();
        }

        if($createRecurrentPayResponse->status == BaseResponse::STATUS_DONE) {
            $paySchet->Status = PaySchet::STATUS_WAITING;
            $paySchet->ExtBillNumber = $createRecurrentPayResponse->transac;
        } else {
            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->ErrorInfo = $createRecurrentPayResponse->message;
        }

        $paySchet->save(false);
        $mutex->release($mutexKey);
        return $paySchet;
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    protected function getUslugatovar()
    {
        return $this->autoPayForm->partner
            ->getUslugatovars()
            ->where([
                'IsCustom' => UslugatovarType::AVTOPLATECOM,
                'IsDeleted' => 0,
            ])
            ->one();
    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @return PaySchet|null
     */
    protected function getReplyRequest(BankAdapterBuilder $bankAdapterBuilder)
    {
        $paySchet = PaySchet::findOne([
            'IdOrg' => $this->autoPayForm->partner->ID,
            'IdUsluga' => $bankAdapterBuilder->getUslugatovar()->ID,
            'Extid' => $this->autoPayForm->extid,
        ]);

        return $paySchet;
    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @return PaySchet
     * @throws CreatePayException
     */
    protected function createPaySchet(BankAdapterBuilder $bankAdapterBuilder, Cards $card)
    {
        $cartToken = new CardToken();
        $token = $cartToken->CheckExistToken($card->CardNumber,$card->getMonth() . $card->getYear());
        if ($token == 0) {
            $token = $cartToken->CreateToken(
                $card->CardNumber,
                $card->getMonth() . $card->getYear(), $card->CardHolder
            );
        }

        $paySchet = new PaySchet();

        $paySchet->Status = PaySchet::STATUS_WAITING;
        $paySchet->IdKard = $card->ID;
        $paySchet->CardNum = Cards::MaskCard($card->CardNumber);
        $paySchet->CardType = Cards::GetCardBrand(Cards::GetTypeCard($card->CardNumber));
        $paySchet->CardHolder = mb_substr($card->CardHolder, 0, 99);
        $paySchet->CardExp = $card->getMonth() . $card->getYear();
        $paySchet->IdShablon = $token;

        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $bankAdapterBuilder->getUslugatovar()->ID;
        $paySchet->IdOrg = $this->autoPayForm->partner->ID;
        $paySchet->Extid = $this->autoPayForm->extid;
        $paySchet->QrParams = $this->autoPayForm->descript;
        $paySchet->SummPay = $this->autoPayForm->amount;
        $paySchet->ComissSumm = $bankAdapterBuilder->getUslugatovar()->calcComiss($paySchet->SummPay);
        $paySchet->MerchVozn = $bankAdapterBuilder->getUslugatovar()->calcComissOrg($paySchet->SummPay);
        $paySchet->BankComis = $bankAdapterBuilder->getUslugatovar()->calcBankComis($paySchet->SummPay);
        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->UserUrlInform = $bankAdapterBuilder->getUslugatovar()->UrlInform;
        $paySchet->IsAutoPay = 0;
        $paySchet->sms_accept = 1;

        $paySchet->PostbackUrl = $this->autoPayForm->postbackurl;

        $paySchet->FIO = $this->autoPayForm->fullname;
        $paySchet->Dogovor = $this->autoPayForm->document_id;

        if(!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }

        return $paySchet;
    }
}
