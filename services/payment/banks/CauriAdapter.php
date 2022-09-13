<?php

namespace app\services\payment\banks;

use app\clients\CauriClient;
use app\clients\cauriClient\requests\CardAuthenticateRequest;
use app\clients\cauriClient\requests\CardGetTokenRequest;
use app\clients\cauriClient\requests\CardProcessRecurringRequest;
use app\clients\cauriClient\requests\CardProcessRequest;
use app\clients\cauriClient\requests\TransactionRefundRequest;
use app\clients\cauriClient\requests\TransactionReverseRequest;
use app\clients\cauriClient\requests\TransactionStatusRequest;
use app\clients\cauriClient\requests\UserResolveRequest;
use app\clients\cauriClient\responses\TransactionStatusResponse;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\createPayResponse\AcsRedirectData;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\data\ClientData;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\models\Bank;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\UslugatovarType;
use GuzzleHttp\Exception\GuzzleException;

class CauriAdapter implements IBankAdapter
{
    public static $bank = 8;

    private const AFT_MIN_SUMM = 120000;
    private const EMAIL_DEFAULT = 'cauri@vepay.online';
    private const PUBLIC_IP_ADDRESS = '84.38.187.23';

    /**
     * @var CauriClient
     */
    private $apiClient;

    /**
     * @var PartnerBankGate
     */
    protected $gate;

    /**
     * @inheritDoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;

        $config = \Yii::$app->params['services']['payments']['Cauri'];

        $this->apiClient = new CauriClient(
            $config['url'],
            $partnerBankGate->Login,
            $partnerBankGate->Token
        );
    }

    /**
     * @inheritDoc
     */
    public function getBankId(): int
    {
        return self::$bank;
    }

    /**
     * @inheritdoc
     * @throws BankAdapterResponseException
     */
    public function confirm(DonePayForm $donePayForm): ConfirmPayResponse
    {
        $confirmPayResponse = new ConfirmPayResponse();

        if ($donePayForm->paRes !== null && $donePayForm->md !== null) {
            try {
                $cardAuthenticateResponse = $this->apiClient->cardAuthenticate(new CardAuthenticateRequest(
                    $donePayForm->paRes,
                    $donePayForm->md
                ));
            } catch (GuzzleException $e) {
                \Yii::$app->errorHandler->logException($e);
                throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
            }

            $confirmPayResponse->status = $cardAuthenticateResponse->isSuccess()
                ? BaseResponse::STATUS_DONE
                : BaseResponse::STATUS_ERROR;
            $confirmPayResponse->transac = $cardAuthenticateResponse->getId();
        } else {
            try {
                $transactionStatusResponse = $this->apiClient->transactionStatus(new TransactionStatusRequest(
                    $donePayForm->getPaySchet()->ExtBillNumber,
                    null
                ));
            } catch (GuzzleException $e) {
                \Yii::$app->errorHandler->logException($e);
                throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
            }

            $confirmPayResponse->status = $this->convertStatus($transactionStatusResponse->getStatus());
            $confirmPayResponse->transac = $transactionStatusResponse->getId();
        }

        return $confirmPayResponse;
    }

    /**
     * @inheritdoc
     */
    public function createPay(CreatePayForm $createPayForm, ClientData $clientData): CreatePayResponse
    {
        $createPayResponse = new CreatePayResponse();

        $paySchet = $createPayForm->getPaySchet();

        try {
            /**
             * При вызове userResolve поле email теперь обязательное, но при отсутствии почты можно передать общий email
             */
            $user = $this->apiClient->userResolve(new UserResolveRequest(
                $paySchet->ID,
                null,
                $paySchet->getUserEmail() ?? self::EMAIL_DEFAULT,
                null,
                null,
                $this->getUserIp()
            ));

            $card = $this->apiClient->cardGetToken(new CardGetTokenRequest(
                $createPayForm->CardNumber,
                $createPayForm->CardMonth,
                '20' . $createPayForm->CardYear,
                $createPayForm->CardCVC
            ));
        } catch (GuzzleException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
        }

        $isRegisterCard = $paySchet->RegisterCard || (int)$paySchet->uslugatovar->IsCustom === UslugatovarType::REGCARD;

        try {
            $cardProcessResponse = $this->apiClient->cardProcess(new CardProcessRequest(
                $paySchet->ID,
                'Счет №' . $paySchet->ID,
                $user->getId(),
                $card->getId(),
                PaymentHelper::convertToFullAmount($paySchet->getSummFull()),
                $paySchet->currency->Code,
                $createPayForm->getReturnUrl(),
                $isRegisterCard ? 1 : null,
                $isRegisterCard ? 0 : null,
                $isRegisterCard ? 0 : null
            ));
        } catch (GuzzleException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
        }

        if (!$cardProcessResponse->isSuccess()) {
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            return $createPayResponse;
        }

        $createPayResponse->status = BaseResponse::STATUS_DONE;
        $createPayResponse->isNeed3DSRedirect = false;
        $createPayResponse->transac = $cardProcessResponse->getId();

        if ($cardProcessResponse->getAcs() !== null) {
            $createPayResponse->isNeed3DSVerif = true;
            $createPayResponse->acs = new AcsRedirectData(
                AcsRedirectData::STATUS_OK,
                $cardProcessResponse->getAcs()->getUrl(),
                'POST',
                [
                    'PaReq' => $cardProcessResponse->getAcs()->getParameters()->getPaReq(),
                    'MD' => $cardProcessResponse->getAcs()->getParameters()->getMD(),
                    'TermUrl' => $createPayResponse->getRetUrl($paySchet->ID),
                ]
            );
        } else {
            $createPayResponse->isNeed3DSVerif = false;
        }

        if ($cardProcessResponse->getRecurring() !== null) {
            $createPayResponse->cardRefId = $cardProcessResponse->getRecurring()->getId();
        }

        return $createPayResponse;
    }

    /**
     * @inheritdoc
     * @throws BankAdapterResponseException
     */
    public function checkStatusPay(OkPayForm $okPayForm): CheckStatusPayResponse
    {
        $checkStatusPayResponse = new CheckStatusPayResponse();

        $paySchet = $okPayForm->getPaySchet();

        try {
            $transactionStatusResponse = $this->apiClient->transactionStatus(new TransactionStatusRequest(
                $paySchet->ExtBillNumber,
                null
            ));
        } catch (GuzzleException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
        }

        $checkStatusPayResponse->status = $this->convertStatus($transactionStatusResponse->getStatus());
        $checkStatusPayResponse->cardRefId = $paySchet->CardRefId3DS;
        $checkStatusPayResponse->message = $transactionStatusResponse->getStatus() . ' - ' . $transactionStatusResponse->getResponseCode();
        $checkStatusPayResponse->rcCode = $transactionStatusResponse->getResponseCode();
        $checkStatusPayResponse->transId = $transactionStatusResponse->getId();

        return $checkStatusPayResponse;
    }

    /**
     * @inheritdoc
     * @throws BankAdapterResponseException
     */
    public function recurrentPay(AutoPayForm $autoPayForm): CreateRecurrentPayResponse
    {
        $createRecurrentPayResponse = new CreateRecurrentPayResponse();

        $paySchet = $autoPayForm->paySchet;

        try {
            $cardProcessRecurringResponse = $this->apiClient->cardProcessRecurring(new CardProcessRecurringRequest(
                $paySchet->ID,
                'Оплата по счету №' . $paySchet->ID,
                $autoPayForm->getCard()->ExtCardIDP,
                PaymentHelper::convertToFullAmount($paySchet->getSummFull()),
                $paySchet->currency->Code
            ));
        } catch (GuzzleException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
        }

        $createRecurrentPayResponse->status = $cardProcessRecurringResponse->isSuccess()
            ? BaseResponse::STATUS_DONE
            : BaseResponse::STATUS_ERROR;
        $createRecurrentPayResponse->transac = $cardProcessRecurringResponse->getId();

        return $createRecurrentPayResponse;
    }

    /**
     * @inheritdoc
     * @throws BankAdapterResponseException
     */
    public function refundPay(RefundPayForm $refundPayForm): RefundPayResponse
    {
        $refundPayResponse = new RefundPayResponse();

        $paySchet = $refundPayForm->paySchet;
        $sourcePaySchet = $paySchet->refundSource;

        try {
            $transactionStatusResponse = $this->apiClient->transactionStatus(new TransactionStatusRequest(
                $paySchet->ExtBillNumber,
                null
            ));
        } catch (GuzzleException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
        }

        if ($sourcePaySchet->getSummFull() === $paySchet->getSummFull() && $transactionStatusResponse->isCanReverse()) {
            try {
                $transactionReverseResponse = $this->apiClient->transactionReverse(new TransactionReverseRequest(
                    $paySchet->ExtBillNumber,
                    null,
                    null
                ));
            } catch (GuzzleException $e) {
                \Yii::$app->errorHandler->logException($e);
                throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
            }

            $refundPayResponse->status = $transactionReverseResponse->isSuccess()
                ? BaseResponse::STATUS_DONE
                : BaseResponse::STATUS_ERROR;
            $refundPayResponse->refundType = RefundPayResponse::REFUND_TYPE_REVERSE;
            $refundPayResponse->transactionId = $transactionReverseResponse->getId();
        } else {
            try {
                $transactionRefundResponse = $this->apiClient->transactionRefund(new TransactionRefundRequest(
                    $paySchet->ExtBillNumber,
                    null,
                    PaymentHelper::convertToFullAmount($paySchet->getSummFull()),
                    null
                ));
            } catch (GuzzleException $e) {
                \Yii::$app->errorHandler->logException($e);
                throw new BankAdapterResponseException(\Yii::t('app.payment-errors', BankAdapterResponseException::REQUEST_ERROR_MSG));
            }

            $refundPayResponse->status = $transactionRefundResponse->isSuccess()
                ? BaseResponse::STATUS_DONE
                : BaseResponse::STATUS_ERROR;
            $refundPayResponse->refundType = RefundPayResponse::REFUND_TYPE_REFUND;
            $refundPayResponse->transactionId = $transactionRefundResponse->getId();
        }

        return $refundPayResponse;
    }

    /**
     * @inheritdoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm): OutCardPayResponse
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function getAftMinSum(): int
    {
        return Bank::findOne(self::$bank)->AftMinSum ?? self::AFT_MIN_SUMM;
    }

    /**
     * @inheritdoc
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest): GetBalanceResponse
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

    /**
     * @param string $status
     * @return int
     */
    private function convertStatus(string $status): int
    {
        switch ($status) {
            case TransactionStatusResponse::STATUS_FAIL:
                return BaseResponse::STATUS_ERROR;
            case TransactionStatusResponse::STATUS_PAID:
            case TransactionStatusResponse::STATUS_REVERSED:
            case TransactionStatusResponse::STATUS_REFUNDED:
                return BaseResponse::STATUS_DONE;
            case TransactionStatusResponse::STATUS_PENDING:
            case TransactionStatusResponse::STATUS_AUTHENTICATING:
            case TransactionStatusResponse::STATUS_CHARGEBACK:
            default:
                return BaseResponse::STATUS_CREATED;
        }
    }

    /**
     * По сути костыль для каури, сейчас нет возможности на дев получить реальный ip клиента, в remote_addr адрес из локальной сети
     * TODO убрать как появится возможность получать реальный ip клиента
     *
     * @return string|null
     */
    private function getUserIp(): ?string
    {
        if (\Yii::$app->params['DEVMODE'] === 'Y' || \Yii::$app->params['TESTMODE'] === 'Y') {
            return self::PUBLIC_IP_ADDRESS;
        }

        return \Yii::$app->request->getUserIP();
    }
}
