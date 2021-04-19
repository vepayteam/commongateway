<?php


namespace app\services\payment\payment_strategies;


use app\models\api\Reguser;
use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\services\cards\models\PanToken;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\BRSAdapter;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2DuplicatedException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\MerchantRequestAlreadyExistsException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PayCard;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\payment\PaymentService;
use Yii;
use yii\db\Exception;

class CreatePayStrategy
{
    const BRS_ECOMM_MAX_SUMM = 185000;

    /** @var CreatePayForm */
    protected $createPayForm;

    /** @var PaymentService */
    protected $paymentService;
    /** @var CreatePayResponse  */
    protected $createPayResponse;

    public function __construct(CreatePayForm $payForm)
    {
        $this->createPayForm = $payForm;
        $this->paymentService = Yii::$container->get('PaymentService');
    }

    /**
     * @return PaySchet
     * @throws CreatePayException
     * @throws Exception
     * @throws GateException
     * @throws BankAdapterResponseException
     * @throws Check3DSv2Exception
     * @throws MerchantRequestAlreadyExistsException
     */
    public function exec()
    {
        $paySchet = $this->createPayForm->getPaySchet();

        if($paySchet->isOld()) {
            throw new CreatePayException('Время для оплаты истекло');
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($paySchet->partner, $paySchet->uslugatovar);
        $this->setCardPay($paySchet, $bankAdapterBuilder->getPartnerBankGate());

        try {
            $this->createPayResponse = $bankAdapterBuilder->getBankAdapter()->createPay($this->createPayForm);
        } catch (MerchantRequestAlreadyExistsException $e) {
            $bankAdapterBuilder->getBankAdapter()->reRequestingStatus($paySchet);
        }
        if(in_array($this->createPayResponse->status, [BaseResponse::STATUS_CANCEL, BaseResponse::STATUS_ERROR])) {
            $this->paymentService->cancelPay($paySchet, $this->createPayResponse->message);
            return $paySchet;
        }

        $this->updatePaySchet($paySchet, $bankAdapterBuilder->getPartnerBankGate());
        return $paySchet;
    }

    /**
     * @param PaySchet $paySchet
     */
    protected function updatePaySchet(PaySchet $paySchet, PartnerBankGate $partnerBankGate)
    {
        $paySchet->Bank = $partnerBankGate->BankId;

        $paySchet->sms_accept = 1;
        $paySchet->UserClickPay = 1;
        $paySchet->UrlFormPay = '/pay/form/' . $paySchet->ID;
        $paySchet->ExtBillNumber = $this->createPayResponse->transac;
        $paySchet->UserEmail = $this->createPayForm->Email;
        $paySchet->CountSendOK = 0;

        $paySchet->Version3DS = $this->createPayResponse->vesion3DS;
        $paySchet->IsNeed3DSVerif = ($this->createPayResponse->isNeed3DSVerif ? 1 : 0);
        $paySchet->AuthValue3DS = $this->createPayResponse->authValue;
        $paySchet->DsTransId = $this->createPayResponse->dsTransId;
        $paySchet->Eci = $this->createPayResponse->eci;
        $paySchet->CardRefId3DS = $this->createPayResponse->cardRefId;

        $paySchet->save(false);
    }

    /**
     * @param PaySchet $paySchet
     * @throws Exception
     */
    protected function updatePaySchetWithRegCard(PaySchet $paySchet)
    {
        $payCard = new PayCard();
        $payCard->number = $this->createPayForm->CardNumber;
        $payCard->holder = $this->createPayForm->CardHolder;
        $payCard->expYear = $this->createPayForm->CardYear;
        $payCard->expMonth = $this->createPayForm->CardMonth;
        $payCard->cvv = $this->createPayForm->CardCVC;

        $this->paymentService->tokenizeCard($paySchet, $payCard);
    }


    protected function setCardPay(PaySchet $paySchet, PartnerBankGate $partnerBankGate)
    {
        $cartToken = new CardToken();
        $token = $cartToken->CheckExistToken(
            $this->createPayForm->CardNumber,
            $this->createPayForm->CardMonth.$this->createPayForm->CardYear
        );

        if($paySchet->IdUser) {
            $user = User::findOne(['ID' => $paySchet->IdUser]);
        } else {
            $reguser = new Reguser();
            $user = $reguser->findUser('0', $paySchet->IdOrg . '-' . time(), md5($paySchet->IdOrg . '-' . time()), $paySchet->IdOrg, false);
            $paySchet->IdUser = $user->ID;
        }

        if ($token == 0) {
            $token = $cartToken->CreateToken(
                $this->createPayForm->CardNumber,
                $this->createPayForm->CardMonth . $this->createPayForm->CardYear,
                $this->createPayForm->CardHolder
            );
        }

        $card = $this->createUnregisterCard($token, $user, $partnerBankGate);
        $paySchet->IdKard = $card->ID;
        $paySchet->CardNum = Cards::MaskCard($this->createPayForm->CardNumber);
        $paySchet->CardType = Cards::GetCardBrand(Cards::GetTypeCard($this->createPayForm->CardNumber));
        $paySchet->CardHolder = mb_substr($this->createPayForm->CardHolder, 0, 99);
        $paySchet->CardExp = $this->createPayForm->CardMonth . $this->createPayForm->CardYear;
        $paySchet->IdShablon = $token;

        if(!$paySchet->save()) {
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
        $card->SrokKard = $this->createPayForm->CardMonth . $this->createPayForm->CardYear;
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

    /**
     * @return CreatePayResponse
     */
    public function getCreatePayResponse()
    {
        return $this->createPayResponse;
    }
}