<?php


namespace app\services\payment\payment_strategies;


use app\models\api\Reguser;
use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\User;
use app\services\cards\models\PanToken;
use app\services\payment\banks\bank_adapter_responses\SendP2pResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;

class SendP2pStrategy
{
    /** @var SendP2pForm */
    protected $sendP2pForm;
    /** @var SendP2pResponse */
    public $sendP2pResponse;

    /**
     * SendP2pStrategy constructor.
     * @param SendP2pForm $sendP2pForm
     */
    public function __construct(SendP2pForm $sendP2pForm)
    {
        $this->sendP2pForm = $sendP2pForm;
    }

    /**
     * @return PaySchet
     * @throws CreatePayException
     * @throws \app\services\payment\exceptions\GateException
     */
    public function exec()
    {
        $paySchet = $this->sendP2pForm->paySchet;
        if ($paySchet->isOld()) {
            throw new CreatePayException('Время для оплаты истекло');
        }
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank, $paySchet->currency);
        $this->setCardPay($paySchet, $bankAdapterBuilder->getPartnerBankGate());
        $paySchet->SummPay = $this->sendP2pForm->amount * 100;
        $paySchet->OutCardPan = substr($this->sendP2pForm->outCardPan, 0, 6)
            . '***'
            . substr($this->sendP2pForm->outCardPan, -4);
        $paySchet->save(false);

        $this->sendP2pResponse = $bankAdapterBuilder->getBankAdapter()->sendP2p($this->sendP2pForm);
        $paySchet->ExtBillNumber = $this->sendP2pResponse->transac;
        $paySchet->save(false);
        return $paySchet;
    }

    protected function updateBeforeRequest()
    {
        $this->sendP2pForm->paySchet->SummPay = $this->sendP2pForm->amount * 100;

    }

    /**
     * @throws CreatePayException
     */
    protected function setCardPay(PaySchet $paySchet, PartnerBankGate $partnerBankGate)
    {
        $cartToken = new CardToken();
        $token = $cartToken->CheckExistToken(
            $this->sendP2pForm->cardPan,
            sprintf('%02d', $this->sendP2pForm->cardExpMonth) . substr($this->sendP2pForm->cardExpYear, 2)
        );

        if ($paySchet->IdUser) {
            $user = User::findOne(['ID' => $paySchet->IdUser]);
        } else {
            $reguser = new Reguser();
            $user = $reguser->findUser('0', $paySchet->IdOrg . '-' . time(), md5($paySchet->IdOrg . '-' . time()), $paySchet->IdOrg, false);
            $paySchet->IdUser = $user->ID;
        }

        if ($token == 0) {
            $token = $cartToken->CreateToken(
                $this->sendP2pForm->cardPan,
                sprintf('%02d', $this->sendP2pForm->cardExpMonth) . substr($this->sendP2pForm->cardExpYear, 2),
                $this->sendP2pForm->cardHolder
            );
        }
        $card = $this->createUnregisterCard($token, $user, $partnerBankGate);
        $paySchet->IdKard = $card->ID;
        $paySchet->CardNum = Cards::MaskCard($this->sendP2pForm->cardPan);
        $paySchet->CardHolder = mb_substr($this->sendP2pForm->cardHolder, 0, 99);
        $paySchet->CardExp = $this->sendP2pForm->cardExpMonth . $this->sendP2pForm->cardExpYear;
        $paySchet->IdShablon = $token;

        if (!$paySchet->save()) {
            throw new CreatePayException('Ошибка валидации данных счета');
        }
    }

    /**
     * @param $token
     * @return Cards
     */
    private function createUnregisterCard($token, User $user, PartnerBankGate $partnerBankGate)
    {
        $panToken = PanToken::findOne(['ID' => $token]);

        $cardNumber = $panToken->FirstSixDigits . '******' . $panToken->LastFourDigits;
        $card = new Cards();
        $card->IdUser = $user->ID;
        $card->NameCard = $cardNumber;
        $card->CardNumber = $cardNumber;
        $card->ExtCardIDP = 0;
        $card->CardType = 0;
        $card->SrokKard = $this->sendP2pForm->cardExpMonth . $this->sendP2pForm->cardExpYear;
        $card->CardHolder = mb_substr($this->sendP2pForm->cardHolder, 0, 99);
        $card->Status = 0;
        $card->DateAdd = time();
        $card->Default = 0;
        $card->TypeCard = 0;
        $card->IdPan = $panToken->ID;
        $card->IdBank = $partnerBankGate->BankId;
        $card->IsDeleted = 0;
        $card->save(false);

        return $card;
    }


}
