<?php

namespace app\services\payment\payment_strategies;

use app\models\api\Reguser;
use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\User;
use app\models\PaySchetAcsRedirect;
use app\services\cards\models\PanToken;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\createPayResponse\AcsRedirectData;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\DuplicateCreatePayException;
use app\services\payment\exceptions\FailPaymentException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\MerchantRequestAlreadyExistsException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\models\repositories\CurrencyRepository;
use app\services\payment\PaymentService;
use Yii;
use yii\mutex\FileMutex;

class CreatePayStrategy
{
    const CACHE_PREFIX_LOCK_CREATE_PAY = 'Cache_CreatePayStrategy_Stop_CreatePay_';
    const CACHE_DURATION_LOCK_CREATE_PAY = 60 * 60; // 60 минут

    const MUTEX_PREFIX_LOCK_CREATE_PAY = 'Mutex_CreatePayStrategy_Stop_CreatePay_';
    const MUTEX_TIMEOUT_LOCK_CREATE_PAY = 2; // 2 секунды

    /** @var CreatePayForm */
    protected $createPayForm;
    /** @var PaymentService */
    protected $paymentService;
    /** @var CreatePayResponse */
    protected $createPayResponse;
    /** @var CurrencyRepository */
    private $currencyRepository;
    /** @var FileMutex */
    private $mutex;

    public function __construct(CreatePayForm $payForm)
    {
        $this->createPayForm = $payForm;
        $this->paymentService = Yii::$container->get('PaymentService');
        $this->currencyRepository = new CurrencyRepository();
        $this->mutex = new FileMutex();
    }

    /**
     * @return PaySchet
     * @throws BankAdapterResponseException
     * @throws Check3DSv2Exception
     * @throws CreatePayException
     * @throws GateException
     * @throws DuplicateCreatePayException
     * @throws FailPaymentException
     */
    public function exec(): PaySchet
    {
        $paySchet = $this->createPayForm->getPaySchet();

        if ($paySchet->isOld()) {
            throw new CreatePayException('Время для оплаты истекло');
        }

        // Проверяем можно ли отправлять запрос в банк
        $this->checkCreatePayLock($paySchet);

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank, $paySchet->currency);
        $this->setCardPay($paySchet, $bankAdapterBuilder->getPartnerBankGate());

        try {
            $this->createPayResponse = $bankAdapterBuilder->getBankAdapter()->createPay($this->createPayForm);
        } catch (MerchantRequestAlreadyExistsException $e) {
            $bankAdapterBuilder->getBankAdapter()->reRequestingStatus($paySchet);
        }

        if (in_array($this->createPayResponse->status, [BaseResponse::STATUS_CANCEL, BaseResponse::STATUS_ERROR])) {
            $this->paymentService->cancelPay($paySchet, $this->createPayResponse->message);
            return $paySchet;
        }

        $this->updatePaySchet($paySchet, $bankAdapterBuilder->getPartnerBankGate());

        $acs = $this->createPayResponse->acs;
        if ($acs instanceof AcsRedirectData) {
            $acsRedirect = new PaySchetAcsRedirect();
            $acsRedirect->id = $paySchet->ID;
            $acsRedirect->status = [
                AcsRedirectData::STATUS_OK => PaySchetAcsRedirect::STATUS_OK,
                AcsRedirectData::STATUS_PENDING => PaySchetAcsRedirect::STATUS_PENDING,
            ][$acs->status];
            $acsRedirect->url = $acs->url;
            $acsRedirect->method = $acs->method;
            $acsRedirect->postParameters = $acs->postParameters;
            $acsRedirect->save(false);
        }

        return $paySchet;
    }

    /**
     * @param PaySchet $paySchet
     * @param PartnerBankGate $partnerBankGate
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
        $paySchet->IPAddressUser = Yii::$app->request->remoteIP;

        $paySchet->save(false);
    }

    /**
     * @throws CreatePayException
     */
    protected function setCardPay(PaySchet $paySchet, PartnerBankGate $partnerBankGate)
    {
        $cartToken = new CardToken();
        $token = $cartToken->CheckExistToken(
            $this->createPayForm->CardNumber,
            $this->createPayForm->CardMonth . $this->createPayForm->CardYear
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
                $this->createPayForm->CardNumber,
                $this->createPayForm->CardMonth . $this->createPayForm->CardYear,
                $this->createPayForm->CardHolder
            );
        }
        $card = $this->createUnregisterCard($token, $user, $partnerBankGate);
        $paySchet->IdKard = $card->ID;
        $paySchet->CardNum = Cards::MaskCard($this->createPayForm->CardNumber);
        $paySchet->CardHolder = mb_substr($this->createPayForm->CardHolder, 0, 99);
        $paySchet->CardExp = $this->createPayForm->CardMonth . $this->createPayForm->CardYear;
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
        $card->CardType = Cards::GetTypeCard($cardNumber);
        $card->SrokKard = $this->createPayForm->CardMonth . $this->createPayForm->CardYear;
        $card->CardHolder = mb_substr($this->createPayForm->CardHolder, 0, 99);
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
    public function getCreatePayResponse(): CreatePayResponse
    {
        return $this->createPayResponse;
    }

    /**
     * Проверяем можно ли создавать платеж
     *
     * @param PaySchet $paySchet
     * @throws DuplicateCreatePayException
     */
    private function checkCreatePayLock(PaySchet $paySchet)
    {
        $cacheKey = $this->getCacheKey($paySchet);
        $mutexKey = $this->getMutexKey($paySchet);

        // Открываем mutex
        if ($this->mutex->acquire($mutexKey, self::MUTEX_TIMEOUT_LOCK_CREATE_PAY)) {
            if (Yii::$app->cache->exists($cacheKey)) {
                Yii::error("CreatePayStrategy checkCreatePayLock PaySchet.ID={$paySchet->ID} cache exists throw CreatePayException");

                throw new DuplicateCreatePayException('Платеж в процессе оплаты');
            }

            Yii::$app->cache->set($cacheKey, $paySchet->ID, self::CACHE_DURATION_LOCK_CREATE_PAY);

            // Релизим mutex
            $this->mutex->release($mutexKey);
        } else {
            Yii::error("CreatePayStrategy checkCreatePayLock PaySchet.ID={$paySchet->ID} mutex acquire return false");

            throw new DuplicateCreatePayException('Ошибка проведения платежа');
        }
    }

    /**
     * Снимаем ограничения с проведения платежа
     */
    public function releaseLock()
    {
        $paySchet = $this->createPayForm->getPaySchet();
        if ($paySchet) {
            Yii::info("CreatePayStrategy releaseLock PaySchet.ID={$paySchet->ID}");

            $cacheKey = $this->getCacheKey($paySchet);
            Yii::$app->cache->delete($cacheKey);
        }
    }

    /**
     * @param PaySchet $paySchet
     * @return string
     */
    private function getCacheKey(PaySchet $paySchet): string
    {
        return self::CACHE_PREFIX_LOCK_CREATE_PAY . $paySchet->ID;
    }

    /**
     * @param PaySchet $paySchet
     * @return string
     */
    private function getMutexKey(PaySchet $paySchet): string
    {
        return self::MUTEX_PREFIX_LOCK_CREATE_PAY . $paySchet->ID;
    }
}