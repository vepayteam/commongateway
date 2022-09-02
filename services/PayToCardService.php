<?php

namespace app\services;

use app\helpers\SecurityHelper;
use app\helpers\TokenHelper;
use app\models\api\Reguser;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\PaySchetP2pRepayment;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\merchant\MerchantPayCreateStrategy;
use app\services\payToCardService\CreatePaymentException;
use app\services\payToCardService\data\CreatePaymentData;
use yii\base\Component;

class PayToCardService extends Component
{
    /** @var LanguageService $languageService */
    private $languageService;

    public function init()
    {
        parent::init();

        $this->languageService = \Yii::$app->get(LanguageService::class);
    }


    /**
     * @param Partner $partner
     * @param CreatePaymentData $paymentData
     * @return PaySchet
     * @throws CreatePaymentException
     */
    public function createPayment(Partner $partner, CreatePaymentData $paymentData): PaySchet
    {
        // create recipient card pan token
        $recipientTokenId = TokenHelper::getOrCreateToken($paymentData->recipientCardNumber, null, null);
        if ($recipientTokenId === 0) {
            throw new CreatePaymentException('Unable to create token.', CreatePaymentException::TOKEN_ERROR);
        }

        $uslugatovar = $this->findUslugatovar($partner);
        if ($uslugatovar === null) {
            throw new CreatePaymentException('Uslugatovar not found.', CreatePaymentException::NO_USLUGATOVAR);
        }

        // build bank adapter
        $bankAdapterBuilder = new BankAdapterBuilder();
        try {
            $bankAdapter = $bankAdapterBuilder
                ->build($partner, $uslugatovar, $paymentData->currency)
                ->getBankAdapter();
        } catch (GateException $e) {
            \Yii::warning($e);
            throw new CreatePaymentException("Gate not found (Partner ID:{$partner->ID}).", CreatePaymentException::NO_GATE);
        }


        // create PaySchet
        $paySchet = new PaySchet();
        $paySchet->OutCardPan = Cards::MaskCard($paymentData->recipientCardNumber);

        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $uslugatovar->ID;
        $paySchet->IdOrg = $partner->ID;
        $paySchet->Extid = $paymentData->extId;
        $paySchet->QrParams = $paymentData->description;
        $paySchet->SummPay = $paymentData->amountFractional;
        $paySchet->CurrencyId = $paymentData->currency->Id;
        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->IsAutoPay = 0;
        $paySchet->UserUrlInform = $bankAdapterBuilder->getUslugatovar()->UrlInform;
        $paySchet->TimeElapsed = $paymentData->timeout * 60;

        $paySchet->SuccessUrl = $paymentData->successUrl;
        $paySchet->FailedUrl = $paymentData->failUrl;
        $paySchet->CancelUrl = $paymentData->cancelUrl;
        $paySchet->PostbackUrl = $paymentData->postbackUrl;
        $paySchet->PostbackUrl_v2 = $paymentData->postbackUrlV2;

        $paySchet->sms_accept = 1;
        $paySchet->FIO = $paymentData->fullName;
        $paySchet->Dogovor = $paymentData->documentId;

        /** copied from {@see MerchantPayCreateStrategy::createPaySchet()} */
        if ($paymentData->cardRegistration) {
            $paySchet->RegisterCard = 1;
            $partnerId = $partner->ID;
            $user = (new Reguser())->findUser(
                '0',
                $partnerId . '-' . time(),
                md5($partnerId . '-' . time()),
                $partnerId,
                false
            );
            $paySchet->IdUser = $user->ID ?? 0;
        }

        $paySchet->save(false);


        // create PaySchetP2pRepayment
        $p2pRepayment = new PaySchetP2pRepayment();
        $p2pRepayment->paySchetId = $paySchet->ID;
        $p2pRepayment->recipientPanTokenId = $recipientTokenId;
        if ($paymentData->presetSenderCard) {
            $p2pRepayment->presetSenderPanTokenId = $paymentData->presetSenderCard->panToken->ID;
            $p2pRepayment->presetHash = SecurityHelper::generateUuid();
        }
        $p2pRepayment->save(false);
        $paySchet->populateRelation('p2pRepayment', $p2pRepayment);

        $this->languageService->saveApiLanguage($paySchet->ID, $paymentData->language);

        return $paySchet;
    }


    /**
     * @param Partner $partner
     * @return Uslugatovar|null
     */
    public function findUslugatovar(Partner $partner): ?Uslugatovar
    {
        /** @var Uslugatovar|null $uslugatovar */
        $uslugatovar = $partner
            ->getUslugatovars()
            ->notSoftDeleted()
            ->andWhere(['IsCustom' => UslugatovarType::P2P_REPAYMENT])
            ->one();

        return $uslugatovar;
    }
}