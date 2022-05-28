<?php

namespace app\services\payment\payment_strategies\mfo;

use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\api\Reguser;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\services\cards\models\PanToken;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\NotUniquePayException;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\models\Currency;
use app\services\payment\models\PayCard;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\payment\PaymentService;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;

class MfoOutCardStrategy
{
    /** @var OutCardPayForm */
    private $outCardPayForm;
    /** @var PaymentService */
    protected $paymentService;

    /**
     * @param OutCardPayForm $outCardPayForm
     */
    public function __construct(OutCardPayForm $outCardPayForm)
    {
        $this->outCardPayForm = $outCardPayForm;
    }

    /**
     * @return PaySchet
     * @throws CardTokenException
     * @throws CreatePayException
     * @throws Exception
     * @throws GateException
     * @throws \Vepay\Gateway\Client\Validator\ValidationException
     * @throws NotUniquePayException
     */
    public function exec()
    {
        $mutex = new FileMutex();
        if (!$mutex->acquire($this->outCardPayForm->getMutexKey(), 30)) {
            throw new Exception('MfoOutCardStrategy: error lock!');
        }

        $replyPay = $this->getReplyPay();
        if($replyPay && $this->outCardPayForm->extid) {
            $mutex->release($this->outCardPayForm->getMutexKey());
            throw new NotUniquePayException($replyPay->ID, $replyPay->Extid);
        }

        $uslugatovar = $this->getUslugatovar();
        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }
        $validateErrors = $this->getPaymentService()->validatePaySchetWithUslugatovar($this->outCardPayForm, $uslugatovar);
        if(count($validateErrors) > 0) {
            throw new GateException($validateErrors[0]);
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->outCardPayForm->partner, $uslugatovar, $this->outCardPayForm->getCurrency());

        if(!$this->outCardPayForm->cardnum) {
            throw new CardTokenException('Ошибка при получение номера карты');
        }

        $user = $this->createUser();
        $card = $this->outCardPayForm->getCardOut();
        $token = null;
        $paySchet = null;
        if($card) {
            $cardToken = new CardToken();
            $this->outCardPayForm->cardnum = $cardToken->GetCardByToken($card->IdPan);
        } else {
            $cartToken = new CardToken();
            if (($token = $cartToken->CheckExistToken($this->outCardPayForm->cardnum, 0)) == 0) {
                $token = $cartToken->CreateToken($this->outCardPayForm->cardnum, 0, '');
            }
            if ($token === 0) {
                throw new CardTokenException('Ошибка при формирование токена');
            }
            $card = $this->createUnregisterCard($token, $user);
        }
        $paySchet = $this->createPaySchet($bankAdapterBuilder, $user, $card);
        $this->outCardPayForm->paySchet = $paySchet;
        $outCardPayResponse = $bankAdapterBuilder->getBankAdapter()->outCardPay($this->outCardPayForm);

        if($outCardPayResponse->status == BaseResponse::STATUS_DONE) {
            $paySchet->ExtBillNumber = $outCardPayResponse->trans;
            $paySchet->save(false);
        } else {
            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->ErrorInfo = $outCardPayResponse->message;
            $paySchet->save(false);
            throw new CreatePayException($outCardPayResponse->message);
        }

        return $paySchet;
    }

    /**
     * @return \app\models\payonline\User|bool|false
     * @throws \Exception
     */
    private function createUser()
    {
        $reguser = new Reguser();
        $user = $reguser->findUser(
            '0',
            $this->outCardPayForm->partner->ID . '-' . time() . random_int(100, 999),
            md5($this->outCardPayForm->partner->ID . '-' . time()),
            $this->outCardPayForm->partner->ID, false
        );
        return $user;
    }

    /**
     * @param $token
     * @return Cards
     */
    private function createUnregisterCard($token, User $user)
    {
        $panToken = PanToken::findOne(['ID' => $token]);

        $cardNumber = $panToken->FirstSixDigits . '******' . $panToken->LastFourDigits;
        $card = new Cards();
        $card->IdUser = $user->ID;
        $card->NameCard = $cardNumber;
        $card->CardNumber = $cardNumber;
        $card->ExtCardIDP = 0;
        $card->CardType = Cards::GetTypeCard($cardNumber);
        $card->SrokKard = 0;
        $card->Status = 1;
        $card->DateAdd = time();
        $card->Default = 0;
        $card->TypeCard = 1;
        $card->IdPan = $panToken->ID;
        $card->IdBank = 0;
        $card->IsDeleted = 0;
        $save = $card->save(false);

        return $card;
    }

    /**
     * @return array|PaySchet|null
     */
    private function getReplyPay()
    {
        try {
            Yii::info((array)$this->outCardPayForm, 'getReplyPay');
            return $this->outCardPayForm->extid ? $this->outCardPayForm->partner->getPaySchets()->where([
                    'Extid' => $this->outCardPayForm->extid,
                ])->one() : null;
        } catch (\Exception $e) {
            Yii::error([$e->getMessage(), $e->getTrace(), $e->getFile(), $e->getLine()], 'getReplyPay');
            return null;
        }
    }

    /**
     * @return array|Uslugatovar|null
     */
    private function getUslugatovar()
    {
        return Uslugatovar::find()
            ->where([
                'IDPartner' => $this->outCardPayForm->partner->ID,
                'IsCustom'  => UslugatovarType::TOCARD,
                'IsDeleted' => 0,
            ])
            ->one();
    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @param User $user
     * @param Cards $card
     * @return PaySchet
     * @throws CreatePayException
     */
    private function createPaySchet(BankAdapterBuilder $bankAdapterBuilder, User $user, Cards $card)
    {
        $paySchet = new PaySchet();

        $paySchet->IdKard = $card->ID;
        $paySchet->IdUser = $user->ID;
        $paySchet->CardNum = Cards::MaskCard($this->outCardPayForm->cardnum);
        $paySchet->CardHolder = mb_substr($card->CardHolder, 0, 99);
        $paySchet->CardExp = $card->getMonth() . $card->getYear();
        $paySchet->Status = PaySchet::STATUS_WAITING;
        $paySchet->IdShablon = 0;
        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $bankAdapterBuilder->getUslugatovar()->ID;
        $paySchet->IdOrg = $this->outCardPayForm->partner->ID;
        $paySchet->Extid = $this->outCardPayForm->extid;
        $paySchet->SummPay = $this->outCardPayForm->amount;
        $paySchet->CurrencyId = $this->outCardPayForm->getCurrency()->Id;

        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->UserUrlInform = $bankAdapterBuilder->getUslugatovar()->UrlInform;
        $paySchet->IsAutoPay = 0;
        $paySchet->sms_accept = 1;

        $paySchet->FIO = $this->outCardPayForm->fullname;
        $paySchet->Dogovor = $this->outCardPayForm->document_id;

        if(!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }

        return $paySchet;
    }

    /**
     * @return PaymentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getPaymentService()
    {
        return Yii::$container->get('PaymentService');
    }

}