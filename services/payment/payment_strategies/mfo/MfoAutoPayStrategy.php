<?php


namespace app\services\payment\payment_strategies\mfo;

use app\models\queue\JobPriorityInterface;
use app\services\payment\jobs\RecurrentPayJob;
use Yii;
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
use yii\helpers\Json;
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
        Yii::warning("Get  Uslugatovar ID=$uslugatovar->ID", 'mfo');
        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }

        Yii::warning('Create BankAdapterBuilder autoPay=' . $this->autoPayForm->partner->ID . $this->autoPayForm->extid, 'mfo');
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->autoPayForm->partner, $uslugatovar);

        $mutexKey = $this->autoPayForm->getMutexKey();
        $mutex = new FileMutex();

        Yii::warning('getReplyRequest autoPay=' . $this->autoPayForm->partner->ID . $this->autoPayForm->extid, 'mfo');

        $replyPaySchet = $this->getReplyRequest($bankAdapterBuilder);
        if($replyPaySchet && $replyPaySchet->SummPay == $this->autoPayForm->amount) {
            Yii::warning('getReplyRequest, the payment which is', 'mfo');
            return $replyPaySchet;
        } elseif ($replyPaySchet && $replyPaySchet->SummPay != $this->autoPayForm->amount) {
            $mutex->release($mutexKey);
            Yii::error('getReplyRequest, a non-unique query', 'mfo');
            throw new CreatePayException('Нарушение уникальности запроса');
        }

        if (!$mutex->acquire($mutexKey, 30)) {
            Yii::error('getPaySchetExt: error lock!', 'mfo');
            throw new CreatePayException('getPaySchetExt: error lock!');
        }

        $card = $this->autoPayForm->getCard();
        $cardnum = null;
        if ($this->autoPayForm->getCard()->IdPan > 0) {
            Yii::warning('New CardToken: error lock!', 'mfo');
            $CardToken = new CardToken();
            $cardnum = $CardToken->GetCardByToken($card->IdPan);
        }

        if(!$cardnum) {
            $mutex->release($mutexKey);
            Yii::error('Empty card mfo_pay_auto autoPay=' . $this->autoPayForm->partner->ID . $this->autoPayForm->extid, 'mfo');
            throw new CreatePayException('empty card');
        }
        $this->autoPayForm->getCard()->CardNumber = $cardnum;

        Yii::warning('createPaySchet autoPay=' . $this->autoPayForm->partner->ID . $this->autoPayForm->extid, 'mfo');
        $paySchet = $this->createPaySchet($bankAdapterBuilder, $card);
        $this->autoPayForm->paySchet = $paySchet;

        $jobData = [
            'paySchetId' => $paySchet->ID,
        ];

        Yii::$app->queue->push(new RecurrentPayJob($jobData));
        Yii::warning('RecurrentPayJob add data=' . Json::encode($jobData), 'mfo');
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

        $paySchet->Status = PaySchet::STATUS_NOT_EXEC;
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
        $paySchet->PostbackUrl_v2 = $this->autoPayForm->postbackurl_v2;

        $paySchet->FIO = $this->autoPayForm->fullname;
        $paySchet->Dogovor = $this->autoPayForm->document_id;

        if(!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }

        return $paySchet;
    }
}
