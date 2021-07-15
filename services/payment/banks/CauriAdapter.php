<?php

namespace app\services\payment\banks;

use app\Api\Payment\Cauri\CauriApiFacade;
use app\Api\Payment\Cauri\Responses\TransactionStatusResponse;
use app\services\ident\models\Ident;
use app\services\logs\loggers\CauriLogger;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CauriResolveUserResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\IdentGetStatusResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\cauri\CheckStatusPayRequest;
use app\services\payment\forms\cauri\CreatePayRequest;
use app\services\payment\forms\cauri\OutCardPayRequest;
use app\services\payment\forms\cauri\RecurrentPayRequest;
use app\services\payment\forms\cauri\RefundPayRequest;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Vepay\Cauri\Client\Request\UserResolveRequest;
use Vepay\Cauri\Client\Request\PayoutCreateRequest;
use Vepay\Cauri\Resource\Balance;
use Vepay\Cauri\Resource\Payout;
use Vepay\Gateway\Config;
use Vepay\Gateway\Logger\LoggerInterface;
use Yii;

class CauriAdapter implements IBankAdapter
{
    public const AFT_MIN_SUMM = 120000;
    public const IS_CONFIG_OUT_CARD_PARAMS_CACHE_PREFIX = 'Cauri_IsConfigOutCardParams';

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CHARGED_BACK = 'charged_back';
    public const STATUS_OPENED = 'opened';

    public const ERROR_STATUS_MSG = 'Ошибка проверки статуса'; //TODO: create global error handler
    public const ERROR_USER_MSG = 'Ошибка получения пользователя'; //TODO: create global error handler
    private const ERROR_MSG_REQUEST = BankAdapterResponseException::REQUEST_ERROR_MSG;

    public static $bank = 8;

    /** @var PartnerBankGate */
    protected $gate;

    /**
     * @inheritDoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;

        $config = Config::getInstance();
        $config->logger = CauriLogger::class;
        $config->logLevel = LoggerInterface::TRACE_LOG_LEVEL;
    }

    /**
     * @inheritDoc
     */
    public function getBankId(): int
    {
        return self::$bank;
    }

    /**
     * @param DonePayForm $donePayForm
     * @return ConfirmPayResponse
     * @throws BankAdapterResponseException
     */
    public function confirm(DonePayForm $donePayForm): ConfirmPayResponse
    {
        $checkStatusPayRequest = new CheckStatusPayRequest();
        $confirmPayResponse = new ConfirmPayResponse();
        $checkStatusPayRequest->id = $donePayForm->IdPay;
        $transaction = $this->getTransactionStatus($checkStatusPayRequest);
        $confirmPayResponse->status = $transaction->status;
        $confirmPayResponse->message = $transaction->message;
        if ($transaction->id) {
            $confirmPayResponse->transac = $transaction->id;
        }
        return $confirmPayResponse;
    }

    /**
     * @param CheckStatusPayRequest $checkStatusPayRequest
     * @return TransactionStatusResponse
     * @throws BankAdapterResponseException
     */
    public function getTransactionStatus(
        CheckStatusPayRequest $checkStatusPayRequest
    ): TransactionStatusResponse {
        $transactionStatusResponse = new TransactionStatusResponse();
        try {
            $api = new CauriApiFacade($this->gate);
            $response = $api->getTransactionStatus($checkStatusPayRequest);
            $content = $response->getContent();
            if (!isset($content['status'])) {
                $transactionStatusResponse->status = BaseResponse::STATUS_ERROR;
                $transactionStatusResponse->message = self::ERROR_STATUS_MSG;
                return $transactionStatusResponse;
            }
            $transactionStatusResponse->status = $this->convertStatus($content['status']);
            $transactionStatusResponse->message = $content['reason'] ?? '';
            $transactionStatusResponse->id = $content['id']; // Transaction ID
            if (isset($content['user']) && !empty($content['user']['id'])) {
                $transactionStatusResponse->userId = (int)$content['user']['id']; // Cauri user ID
            }
        } catch (\Exception $e) {
            Yii::error(' CauriAdapter getTransactionStatus err:' . $e->getMessage());
            throw new BankAdapterResponseException(self::ERROR_MSG_REQUEST);
        }
        return $transactionStatusResponse;
    }

    /**
     * @inheritDoc
     */
    public function confirmPay($idpay, $org = 0, $isCron = false)
    {
        // TODO: Implement confirmPay() method.
    }

    /**
     * @inheritDoc
     */
    public function transferToCard(array $data)
    {
        // TODO: Implement transferToCard() method.
    }

    /**
     * @param CreatePayForm $createPayForm
     * @return CreatePayResponse
     * @throws CreatePayException
     */
    public function createPay(CreatePayForm $createPayForm): CreatePayResponse
    {
        $createPayResponse = new CreatePayResponse();
        $paySchet = $createPayForm->getPaySchet();
        $user = $this->getResolveUser($paySchet); // Get unique Banks user id
        if (!$user->id) {
            $createPayResponse->status = $user->status;
            $createPayResponse->message = BankAdapterResponseException::setErrorMsg($user->message);
            return $createPayResponse;
        }
        $createPayRequest = $this->formatCreatePayRequest($createPayForm, $user->id);
        try {
            $api = new CauriApiFacade($this->gate);
            $response = $api->payInCreate($createPayRequest);
            $content = $response->getContent();
            $status = $content['status'];
            if (!isset($content['id']) || $status === self::STATUS_FAILED) {
                $createPayResponse->status = BaseResponse::STATUS_ERROR;
                $createPayResponse->message = BankAdapterResponseException::setErrorMsg($content['reason'] ?? '');
                return $createPayResponse;
            }
            // success and have no 3DS
            if ($status === self::STATUS_COMPLETED && !array_key_exists('acs', $content)) {
                $createPayResponse->isNeed3DSRedirect = false;
                $createPayResponse->status = BaseResponse::STATUS_DONE;
                $createPayResponse->transac = $content['id'];
                return $createPayResponse;
            }
            if (isset($content['acs']) && !array_key_exists('parameters', $content['acs'])) {
                Yii::error('CauriAdapter payInCreate err: ' . $content);
                throw new CreatePayException('CauriAdapter Empty 3ds url');
            }
            //3DS redirect
            $createPayResponse->isNeed3DSRedirect = false;
            $createPayResponse->isNeed3DSVerif = true;
            $createPayResponse->status = BaseResponse::STATUS_DONE;
            $createPayResponse->transac = $content['id'];
            $createPayResponse->url = $content['acs']['url'];
            $createPayResponse->md = $content['acs']['parameters']['MD'];
            $createPayResponse->pa = $content['acs']['parameters']['PaReq'];
        } catch (\Exception $e) {
            Yii::error('CauriAdapter payInCreate err: ' . $e->getMessage());
            throw new CreatePayException(self::ERROR_MSG_REQUEST . ': ' . $e->getMessage());
        }

        return $createPayResponse;
    }

    /**
     * Format payIn Request
     * @param CreatePayForm $createPayForm
     * @param int $user
     * @return CreatePayRequest
     */
    private function formatCreatePayRequest(
        CreatePayForm $createPayForm,
        int $user
    ): CreatePayRequest {
        $paySchet = $createPayForm->getPaySchet();
        $createPayRequest = new CreatePayRequest();
        $createPayRequest->user = $user;
        $createPayRequest->order_id = $paySchet->ID;
        $createPayRequest->description = 'Счет №' . $paySchet->ID ?? '';
        $createPayRequest->price = PaymentHelper::convertToFullAmount($paySchet->getSummFull());
        $createPayRequest->acs_return_url = $createPayForm->getReturnUrl();
        //card details
        $createPayRequest->card = [
            'number' => $createPayForm->CardNumber,
            'expiration_month' => $createPayForm->CardMonth,
            'expiration_year' => $createPayForm->CardYear,
            'security_code' => $createPayForm->CardCVC,
            'holder' => $createPayForm->CardHolder,
        ];
        return $createPayRequest;
    }

    /**
     * @param PaySchet $paySchet
     * @return array
     */
    public function formatResolveUserRequest(PaySchet $paySchet): array
    {
        return [
            'ip' => Yii::$app->request->remoteIP,
            'identifier' => $paySchet->ID, // Unique identifier
        ];
    }

    /**
     * @param PaySchet $paySchet
     * @param int $userId
     * @return RecurrentPayRequest
     */
    private function formatRecurrentPayRequest(PaySchet $paySchet, int $userId): RecurrentPayRequest
    {
        $recurrentPayRequest = new RecurrentPayRequest();
        $recurrentPayRequest->order_id = $paySchet->ID; // Order ID will be returned back in a callback
        $recurrentPayRequest->user = $userId;
        $recurrentPayRequest->price = PaymentHelper::convertToFullAmount($paySchet->getSummFull());
        $recurrentPayRequest->description = 'Оплата по счету №' . $paySchet->ID;
        return $recurrentPayRequest;
    }

    /**
     *  Get banks user id
     * @param PaySchet $paySchet
     * @return CauriResolveUserResponse
     */
    private function getResolveUser(PaySchet $paySchet): CauriResolveUserResponse
    {
        $data = $this->formatResolveUserRequest($paySchet);
        $userResolveRequest = new UserResolveRequest($data);
        $userResponse = new CauriResolveUserResponse();

        try {
            $api = new CauriApiFacade($this->gate);
            $response = $api->resolveUser($userResolveRequest);
            $content = $response->getContent();
            if (!isset($content['id'])) {
                $userResponse->status = BaseResponse::STATUS_ERROR;
                $userResponse->message = BankAdapterResponseException::setErrorMsg(self::ERROR_USER_MSG);
                return $userResponse;
            }
        } catch (\Exception $e) {
            Yii::error('CauriAdapter resolveUser err: ' . $e->getMessage());
            $userResponse->status = BaseResponse::STATUS_ERROR;
            $userResponse->message = $e->getMessage();
            return $userResponse;
        }
        $userResponse->id = (int)$content['id'];
        return $userResponse;
    }

    /**
     * @inheritDoc
     */
    public function PayXml(array $params)
    {
        // TODO: Implement PayXml() method.
    }

    /**
     * @inheritDoc
     */
    public function PayApple(array $params)
    {
        // TODO: Implement PayApple() method.
    }

    /**
     * @inheritDoc
     */
    public function PayGoogle(array $params)
    {
        // TODO: Implement PayGoogle() method.
    }

    /**
     * @inheritDoc
     */
    public function PaySamsung(array $params)
    {
        // TODO: Implement PaySamsung() method.
    }

    /**
     * @inheritDoc
     */
    public function ConfirmXml(array $params)
    {
        // TODO: Implement ConfirmXml() method.
    }

    /**
     * @inheritDoc
     */
    public function reversOrder($IdPay)
    {
        // TODO: Implement reversOrder() method & add at Cauri API
    }

    /**
     * @param OkPayForm $okPayForm
     * @return CheckStatusPayResponse
     * @throws BankAdapterResponseException
     */
    public function checkStatusPay(OkPayForm $okPayForm): CheckStatusPayResponse
    {
        $checkStatusPayRequest = new CheckStatusPayRequest();
        $checkStatusPayRequest->id = $okPayForm->getPaySchet()->ExtBillNumber; // id of transaction
        $transactionResponse = $this->getTransactionStatus($checkStatusPayRequest);
        $checkStatusPayResponse = new CheckStatusPayResponse();
        $checkStatusPayResponse->status = $transactionResponse->status;
        $checkStatusPayResponse->message = $transactionResponse->message;
        if ($transactionResponse->userId) {
            $checkStatusPayResponse->cardRefId = $transactionResponse->userId;
        }
        return $checkStatusPayResponse;
    }

    /**
     * @param AutoPayForm $autoPayForm
     * @return CreateRecurrentPayResponse
     * @throws BankAdapterResponseException
     */
    public function recurrentPay(AutoPayForm $autoPayForm): CreateRecurrentPayResponse
    {
        $paySchet = $autoPayForm->paySchet;
        $createRecurrentPayResponse = new CreateRecurrentPayResponse();
        $banksUserIdentification = $autoPayForm->getCard()->ExtCardIDP;
        $userId = $banksUserIdentification;
        if (isset($banksUserIdentification) && $banksUserIdentification == 0) {
            $user = $this->getResolveUser($paySchet); // Get unique Banks user id
            if (!$user->id) {
                $createRecurrentPayResponse->status = $user->status;
                $createRecurrentPayResponse->message = BankAdapterResponseException::setErrorMsg($user->message);
                return $createRecurrentPayResponse;
            }
            $userId = $user->id;
        }
        /** @var RecurrentPayRequest */
        $recurrentPayRequest = $this->formatRecurrentPayRequest($paySchet, $userId);
        try {
            $api = new CauriApiFacade($this->gate);
            $response = $api->cardManualRecurring($recurrentPayRequest);
            $content = $response->getContent();
            if (!isset($content['orderId']) || $content['status'] === self::STATUS_FAILED) {
                $reason = $content['reason'] ?? '';
                $createRecurrentPayResponse->status = BaseResponse::STATUS_ERROR;
                $createRecurrentPayResponse->message = BankAdapterResponseException::setErrorMsg($reason);
                return $createRecurrentPayResponse;
            }
        } catch (\Exception $e) {
            Yii::error(' CauriApi recurrentPay err:' . $e->getMessage());
            throw new BankAdapterResponseException(self::ERROR_MSG_REQUEST . ': ' . $e->getMessage());
        }

        $createRecurrentPayResponse->status = $this->convertStatus($content['status']);
        $createRecurrentPayResponse->transac = $content['orderId'];
        return $createRecurrentPayResponse;
    }

    /**
     * @param RefundPayForm $refundPayForm
     * @return RefundPayResponse
     * @throws BankAdapterResponseException
     */
    public function refundPay(RefundPayForm $refundPayForm): RefundPayResponse
    {
        $refundPayRequest = new RefundPayRequest();
        $refundPayRequest->id = $refundPayForm->paySchet->ExtBillNumber; // Banks transaction ID
        $refundPayRequest->amount = PaymentHelper::convertToFullAmount($refundPayForm->paySchet->getSummFull());
        $refundPayResponse = new RefundPayResponse();

        try {
            $api = new CauriApiFacade($this->gate);
            $response = $api->refundCreate($refundPayRequest);
            $content = $response->getContent();
            if (!isset($content['id']) || $content['status'] === self::STATUS_FAILED) {
                $refundPayResponse->status = BaseResponse::STATUS_ERROR;
                $refundPayResponse->message = BankAdapterResponseException::setErrorMsg($content['reason'] ?? '');
                return $refundPayResponse;
            }
        } catch (\Exception $e) {
            Yii::error(' CauriApi refundPay err:' . $e->getMessage());
            throw new BankAdapterResponseException(self::ERROR_MSG_REQUEST . ': ' . $e->getMessage());
        }
        $refundPayResponse->status = $this->convertStatus($content['status']);
        $refundPayResponse->message = '';
        return $refundPayResponse;
    }

    /**
     * @param OutCardPayForm $outCardPayForm
     * @return OutCardPayResponse
     */
    public function outCardPay(OutCardPayForm $outCardPayForm): OutCardPayResponse
    {
        $outCardPayRequest = new OutCardPayRequest();
        $outCardPayRequest->amount = $outCardPayForm->amount / 100;
        $outCardPayRequest->description = 'Выдача на карту №' . $outCardPayForm->paySchet->ID;
        $outCardPayRequest->orderId = $outCardPayForm->paySchet->ID;
        $outCardPayRequest->account = $outCardPayForm->cardnum;
        $outCardPayRequest->beneficiaryFirstName = $outCardPayForm->getFirstName();
        $outCardPayRequest->beneficiaryLastName = $outCardPayForm->getLastName();

        $outCardPayRequest->birthDate = $outCardPayForm->birthDate;
        $outCardPayRequest->countryOfCitizenship = $outCardPayForm->countryOfCitizenship;
        $outCardPayRequest->countryOfResidence = $outCardPayForm->countryOfResidence;
        $outCardPayRequest->documentType = $outCardPayForm->documentType;
        $outCardPayRequest->documentIssuedAt = $outCardPayForm->documentIssuedAt;
        $outCardPayRequest->documentValidUntil = $outCardPayForm->documentValidUntil;
        $outCardPayRequest->birthPlace = $outCardPayForm->birthPlace;
        $outCardPayRequest->documentIssuer = $outCardPayForm->documentIssuer;
        $outCardPayRequest->documentSeries = $outCardPayForm->documentSeries;
        $outCardPayRequest->documentNumber = $outCardPayForm->documentNumber;
        $outCardPayRequest->phone = $outCardPayForm->phone;

        $payout = new Payout();
        $response = $payout->__call('create', [
            $outCardPayRequest->getAttributes(), [
                'public_key' => $this->gate->Login,
                'private_key' => $this->gate->Token,
            ]
        ]);

        $content = $response->getContent();
        $outCardPayResponse = new OutCardPayResponse();

        if(array_key_exists('id', $content) && !empty($content['id'])) {
            $outCardPayResponse->status = BaseResponse::STATUS_DONE;
            $outCardPayResponse->trans = $content['id'];
        } else {
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = 'Ошибка запроса';
        }

        return $outCardPayResponse;
    }

    /**
     * @param string $status
     * @return int
     */
    public function convertStatus(string $status): int
    {
        switch ($status) {
            case self::STATUS_OPENED:
            case self::STATUS_CHARGED_BACK:
                return BaseResponse::STATUS_CREATED;
            case self::STATUS_COMPLETED:
                return BaseResponse::STATUS_DONE;
            case self::STATUS_REFUNDED:
                return BaseResponse::STATUS_CANCEL;
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    public function getAftMinSum(): int
    {
        return self::AFT_MIN_SUMM;
    }

    /**
     * @inheritDoc
     * @throws BankAdapterResponseException
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest): GetBalanceResponse
    {
        $getBalanceResponse = new GetBalanceResponse();
        try {
            $api = new CauriApiFacade($this->gate);
            $responseData = $api->getBalance($getBalanceRequest->getAttributes());
        } catch (\Exception $e) {
            throw new BankAdapterResponseException('Ошибка запроса, попробуйте повторить позднее');
        }
        $response = $responseData->getContent();
        if (!isset($response['amount']) || empty($response['amount'])) {
            Yii::warning("Balance service:: Cauri request failed for currency: $getBalanceRequest->currency");
            return $getBalanceResponse;
        }
        $getBalanceResponse->bank_name = $getBalanceRequest->bankName;
        $getBalanceResponse->amount = round((float)$response['amount'], 2);
        $getBalanceResponse->currency = $response['currency'];
        $getBalanceResponse->account_type = $getBalanceRequest->accountType;
        return $getBalanceResponse;
    }

    /**
     * @inheritDoc
     */
    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function identInit(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritDoc
     */
    public function identGetStatus(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @throws GateException
     */
    public function currencyExchangeRates()
    {
        throw new GateException('Метод недоступен');
    }
}
