<?php

namespace app\services\payment\banks;

use app\clients\PaylerClient;
use app\clients\paylerClient\PaylerException;
use app\clients\paylerClient\requests\ChallengeCompleteRequest;
use app\clients\paylerClient\requests\CreditMerchantRequest;
use app\clients\paylerClient\requests\GetStatusRequest;
use app\clients\paylerClient\requests\PayMerchantRequest;
use app\clients\paylerClient\requests\RefundRequest;
use app\clients\paylerClient\requests\RepeatPayRequest;
use app\clients\paylerClient\requests\Send3dsRequest;
use app\clients\paylerClient\requests\ThreeDsMethodCompleteRequest;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\createPayResponse\AcsRedirectData;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\data\ClientData;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\CreatePaySecondStepForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\interfaces\Issuer3DSVersionInterface;
use app\services\payment\models\Bank;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\UslugatovarType;

class PaylerAdapter implements IBankAdapter, IBankSecondStepInterface
{
    public static $bank = 17;

    /**
     * В случае если email отсутствует у клиента
     */
    private const EMAIL_DEFAULT = 'payler@vepay.online';

    /**
     * @var PaylerClient
     */
    private $api;

    /**
     * @inheritdoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $config = \Yii::$app->params['services']['payments']['Payler'];

        $this->api = new PaylerClient($config['url'], $partnerBankGate->Login, $partnerBankGate->Password);
    }

    /**
     * @inheritdoc
     */
    public function getBankId(): int
    {
        return static::$bank;
    }

    /**
     * @inheritdoc
     */
    public function confirm(DonePayForm $donePayForm): ConfirmPayResponse
    {
        $confirmPayResponse = new ConfirmPayResponse();

        $paySchet = $donePayForm->getPaySchet();

        if ($paySchet->Version3DS === Issuer3DSVersionInterface::V_20) {
            try {
                $this->api->challengeComplete(new ChallengeCompleteRequest(
                    $donePayForm->cres
                ));
            } catch (PaylerException $e) {
                \Yii::$app->errorHandler->logException($e);

                throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
            }
        } else if ($paySchet->Version3DS === Issuer3DSVersionInterface::V_1) {
            try {
                $this->api->send3ds(new Send3dsRequest(
                    $donePayForm->paRes,
                    $donePayForm->md
                ));
            } catch (PaylerException $e) {
                \Yii::$app->errorHandler->logException($e);

                throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
            }
        }

        $confirmPayResponse->status = BaseResponse::STATUS_CREATED;

        return $confirmPayResponse;
    }

    /**
     * @inheritdoc
     */
    public function createPay(CreatePayForm $createPayForm, ClientData $clientData): CreatePayResponse
    {
        $createPayResponse = new CreatePayResponse();

        $paySchet = $createPayForm->getPaySchet();

        $isRegisterCard = $paySchet->RegisterCard || (int)$paySchet->uslugatovar->IsCustom === UslugatovarType::REGCARD;

        try {
            $payMerchantResponse = $this->api->payMerchant(new PayMerchantRequest(
                $paySchet->ID,
                $paySchet->currency->Code,
                $paySchet->getSummFull(),
                $createPayForm->CardNumber,
                $createPayForm->CardHolder,
                $createPayForm->CardYear,
                $createPayForm->CardMonth,
                $createPayForm->CardCVC,
                $paySchet->getUserEmail() ?? self::EMAIL_DEFAULT,
                \Yii::$app->request->getUserIP(),
                $clientData->headerAccept,
                $clientData->browserLanguage,
                $clientData->headerUserAgent,
                $clientData->browserJavaEnabled ?? false,
                true,
                $clientData->browserScreenHeight,
                $clientData->browserScreenWidth,
                $clientData->browserColorDepth,
                $clientData->browserTimezoneOffset,
                $paySchet->getOrderdoneUrl(),
                $isRegisterCard ? 1 : null
            ));
        } catch (PaylerException $e) {
            \Yii::$app->errorHandler->logException($e);

            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = $e->getErrorResponse() !== null
                ? $e->getErrorResponse()->message
                : \Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG);

            return $createPayResponse;
        }

        $createPayResponse->status = BaseResponse::STATUS_CREATED;

        if ($payMerchantResponse->threeDSMethodUrl !== null && $payMerchantResponse->threeDSServerTransID !== null) {
            // TODO rewrite to acs redirect iframe
            $createPayResponse->vesion3DS = Issuer3DSVersionInterface::V_20;
            $createPayResponse->dsTransId = $payMerchantResponse->threeDSServerTransID;
            $createPayResponse->isNeedSendTransIdTKB = true;
            $createPayResponse->termurl = $createPayResponse->getStep2Url($paySchet->ID);
            $createPayResponse->threeDSMethodURL = $payMerchantResponse->threeDSMethodUrl;
            $createPayResponse->threeDSServerTransID = $payMerchantResponse->threeDSServerTransID;
        } else if ($payMerchantResponse->acsUrl !== null && $payMerchantResponse->cReq !== null) {
            $createPayResponse->vesion3DS = Issuer3DSVersionInterface::V_20;
            $createPayResponse->acs = new AcsRedirectData(
                AcsRedirectData::STATUS_OK,
                $payMerchantResponse->acsUrl,
                'POST',
                [
                    'creq' => $payMerchantResponse->cReq,
                ]
            );
        } else if (
            $payMerchantResponse->acsUrl !== null &&
            $payMerchantResponse->paReq !== null &&
            $payMerchantResponse->md !== null
        ) {
            $createPayResponse->vesion3DS = Issuer3DSVersionInterface::V_1;
            $createPayResponse->acs = new AcsRedirectData(
                AcsRedirectData::STATUS_OK,
                $payMerchantResponse->acsUrl,
                'POST',
                [
                    'PaReq' => $payMerchantResponse->paReq,
                    'MD' => $payMerchantResponse->md,
                    'TermUrl' => $createPayResponse->getRetUrl($paySchet->ID),
                ]
            );
        } else {
            // Транзакция прошла по frictionless flow, 3ds не требуется

            $createPayResponse->status = BaseResponse::STATUS_DONE;
            $createPayResponse->acs = new AcsRedirectData(
                AcsRedirectData::STATUS_OK,
                $createPayResponse->getRetUrl($paySchet->ID),
                'POST'
            );
        }

        return $createPayResponse;
    }

    /**
     * @inheritdoc
     */
    public function createPayStep2(CreatePaySecondStepForm $createPaySecondStepForm): CreatePayResponse
    {
        $createPayResponse = new CreatePayResponse();

        try {
            $threeDsMethodCompleteResponse = $this->api->threeDsMethodComplete(new ThreeDsMethodCompleteRequest(
                'Y',
                $createPaySecondStepForm->getPaySchet()->DsTransId
            ));
        } catch (PaylerException $e) {
            \Yii::$app->errorHandler->logException($e);

            throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
        }

        if ($threeDsMethodCompleteResponse->acsUrl !== null && $threeDsMethodCompleteResponse->cReq !== null) {
            $createPayResponse->isNeed3DSVerif = true;
            $createPayResponse->url = $threeDsMethodCompleteResponse->acsUrl;
            $createPayResponse->creq = $threeDsMethodCompleteResponse->cReq;
        } else {
            $createPayResponse->isNeed3DSVerif = false;
        }

        return $createPayResponse;
    }

    /**
     * @inheritdoc
     */
    public function checkStatusPay(OkPayForm $okPayForm): CheckStatusPayResponse
    {
        $checkStatusPayResponse = new CheckStatusPayResponse();

        $paySchet = $okPayForm->getPaySchet();

        try {
            $getStatusResponse = $this->api->getStatus(new GetStatusRequest(
                $paySchet->isRefund ? $paySchet->refundSource->ID : $paySchet->ID
            ));
        } catch (PaylerException $e) {
            \Yii::$app->errorHandler->logException($e);

            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = $e->getErrorResponse() !== null
                ? $e->getErrorResponse()->message
                : BankAdapterResponseException::REQUEST_ERROR_MSG;
            $checkStatusPayResponse->rcCode = $e->getErrorResponse() !== null
                ? $e->getErrorResponse()->code
                : null;

            return $checkStatusPayResponse;
        }

        switch ($getStatusResponse->status) {
            case 'Reversed':
            case 'Refunded':
            case 'Charged':
            case 'Credited':
                $checkStatusPayResponse->status = BaseResponse::STATUS_DONE;
                break;
            case 'Rejected':
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
                break;
            default:
                $checkStatusPayResponse->status = BaseResponse::STATUS_CREATED;
                break;
        }

        return $checkStatusPayResponse;
    }

    /**
     * @inheritdoc
     */
    public function recurrentPay(AutoPayForm $autoPayForm): CreateRecurrentPayResponse
    {
        $createRecurrentPayResponse = new CreateRecurrentPayResponse();

        $paySchet = $autoPayForm->paySchet;

        try {
            $this->api->repeatPay(new RepeatPayRequest(
                $paySchet->ID,
                $paySchet->getSummFull(),
                $paySchet->cards->ExtCardIDP,
                $paySchet->currency->Code
            ));
        } catch (PaylerException $e) {
            \Yii::$app->errorHandler->logException($e);

            $createRecurrentPayResponse->status = BaseResponse::STATUS_ERROR;
            $createRecurrentPayResponse->message = $e->getErrorResponse() !== null
                ? $e->getErrorResponse()->message
                : \Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG);

            return $createRecurrentPayResponse;
        }

        $createRecurrentPayResponse->status = BaseResponse::STATUS_DONE;

        return $createRecurrentPayResponse;
    }

    /**
     * @inheritdoc
     */
    public function refundPay(RefundPayForm $refundPayForm): RefundPayResponse
    {
        $refundPayResponse = new RefundPayResponse();

        $paySchet = $refundPayForm->paySchet;

        try {
            $this->api->refund(new RefundRequest(
                $paySchet->refundSource->ID,
                $paySchet->getSummFull()
            ));
        } catch (PaylerException $e) {
            \Yii::$app->errorHandler->logException($e);

            $refundPayResponse->status = BaseResponse::STATUS_ERROR;
            $refundPayResponse->message = $e->getErrorResponse() !== null
                ? $e->getErrorResponse()->message
                : \Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG);

            return $refundPayResponse;
        }

        $refundPayResponse->status = BaseResponse::STATUS_CREATED;

        return $refundPayResponse;
    }

    /**
     * @inheritdoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm): OutCardPayResponse
    {
        $outCardPayResponse = new OutCardPayResponse();

        $paySchet = $outCardPayForm->paySchet;

        try {
            $this->api->creditMerchant(new CreditMerchantRequest(
                $paySchet->ID,
                $outCardPayForm->cardnum,
                $paySchet->getSummFull(),
                $outCardPayForm->email ?? self::EMAIL_DEFAULT,
                $paySchet->currency->Code,
                $outCardPayForm->cardHolderName ?? null
            ));
        } catch (PaylerException $e) {
            \Yii::$app->errorHandler->logException($e);

            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = $e->getErrorResponse() !== null
                ? $e->getErrorResponse()->message
                : \Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG);

            return $outCardPayResponse;
        }

        $outCardPayResponse->status = BaseResponse::STATUS_CREATED;

        return $outCardPayResponse;
    }

    /**
     * @inheritdoc
     */
    public function getAftMinSum(): int
    {
        return Bank::findOne(self::$bank)->AftMinSum ?? 0;
    }

    /**
     * @inheritdoc
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function identInit(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function identGetStatus(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function currencyExchangeRates()
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function registrationBenific(RegistrationBenificForm $registrationBenificForm)
    {
        throw new GateException('Метод недоступен');
    }
}