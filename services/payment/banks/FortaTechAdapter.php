<?php


namespace app\services\payment\banks;


use app\Api\Client\Client;
use app\helpers\SignatureHelper;
use app\helpers\signatureHelper\SignatureException;
use app\models\TU;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\ConfirmPayResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\bank_adapter_responses\CreateRecurrentPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\bank_adapter_responses\RegistrationBenificResponse;
use app\services\payment\banks\bank_adapter_responses\TransferToAccountResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\FortaClientException;
use app\services\payment\exceptions\FortaGatewayTimeoutException;
use app\services\payment\exceptions\FortaServerException;
use app\services\payment\exceptions\FortaSignatureException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\forta\CreatePayRequest;
use app\services\payment\forms\forta\OutCardPayRequest;
use app\services\payment\forms\forta\PaymentRequest;
use app\services\payment\forms\forta\RecurrentPayRequest;
use app\services\payment\forms\forta\RefundPayRequest;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\helpers\TimeHelper;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\traits\MaskableTrait;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\helpers\Json;

class FortaTechAdapter implements IBankAdapter
{
    use MaskableTrait;

    const AFT_MIN_SUMM = 120000;
    const BANK_URL = 'https://pay1time.com';
    const BANK_URL_TEST = 'https://pay1time.com';

    const REFUND_ID_CACHE_PREFIX = 'Forta__RefundIds__';
    const REFUND_REFRESH_STATUS_JOB_DELAY = 30;

    const DB_SESSION_EXCEPTION_MESSAGE = 'Startup of infobase session is not allowed';
    const ERROR_MESSAGE_COMMON = 'Ошибка проведения платежа. Пожалуйста, повторите попытку позже';

    /** Interval in seconds between status refresh requests for recurrent payments. */
    private const RECURRENT_REFRESH_STATUS_INTERVAL = 2;

    public static $bank = 9;
    protected $bankUrl;
    /** @var PartnerBankGate */
    protected $gate;
    /** @var Client */
    protected $api;
    protected $errorMatchList = [
        'failed to connect to' => 'Не удалось связаться с провайдером',
    ];

    /**
     * @inheritDoc
     */
    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;
        if (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] == 'Y') {
            $this->bankUrl = self::BANK_URL_TEST;
        } else {
            $this->bankUrl = self::BANK_URL;
        }
        $apiClientHeader = [
            'Authorization' => 'Token: ' . $this->gate->Token,
        ];
        $config = [
            RequestOptions::HEADERS => $apiClientHeader,
        ];
        $infoMessage = sprintf(
            'partnerId=%d bankId=%d',
            $this->gate->PartnerId,
            $this->getBankId()
        );
        $this->api = new Client($config, $infoMessage);
    }

    /**
     * @inheritDoc
     */
    public function getBankId()
    {
        return self::$bank;
    }

    /**
     * @inheritDoc
     * @throws BankAdapterResponseException
     */
    public function confirm(DonePayForm $donePayForm)
    {
        $checkStatusPayResponse = $this->getCommonStatusPay($donePayForm->getPaySchet());

        $confirmPayResponse = new ConfirmPayResponse();
        $confirmPayResponse->status = $checkStatusPayResponse->status;
        $confirmPayResponse->message = $checkStatusPayResponse->message;

        return $confirmPayResponse;
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

    }

    /**
     * @inheritDoc
     */
    public function createPay(CreatePayForm $createPayForm)
    {
        $createPayResponse = new CreatePayResponse();

        $action = '/api/payments';
        $paySchet = $createPayForm->getPaySchet();
        $paymentRequest = new PaymentRequest();
        $paymentRequest->order_id = (string)$paySchet->ID;
        $paymentRequest->amount = $paySchet->getSummFull();
        $paymentRequest->processing_url = $paySchet->getOrderdoneUrl();
        $paymentRequest->return_url = $paySchet->getOrderdoneUrl();
        $paymentRequest->fail_url = $paySchet->getOrderfailUrl();
        $paymentRequest->callback_url = $paySchet->getCallbackUrl();
        $paymentRequest->ttl = TimeHelper::secondsToHoursCeil($paySchet->TimeElapsed);

        try {
            $ans = $this->sendRequest($action, $paymentRequest->getAttributes());
            if(!array_key_exists('id', $ans) || empty($ans['id'])) {
                throw new CreatePayException('FortaTechAdapter Empty ExtBillNumber');
            }
        } catch (FortaServerException|FortaClientException|BankAdapterResponseException $e) {
            Yii::$app->errorHandler->logException($e);
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = $e->getMessage();
            return $createPayResponse;
        }

        $transId = $ans['id'];
        $action = '/payWithoutForm/' . $transId;
        $createPayRequest = new CreatePayRequest();
        $createPayRequest->cardNumber = (string)$createPayForm->CardNumber;
        $createPayRequest->cardHolder = $createPayForm->CardHolder;
        $createPayRequest->expireMonth = $createPayForm->CardMonth;
        $createPayRequest->expireYear = $createPayForm->CardYear;
        $createPayRequest->cvv = $createPayForm->CardCVC;

        try {
            $ans = $this->sendRequest($action, $createPayRequest->getAttributes());
        } catch (FortaGatewayTimeoutException $e) {
            Yii::error('FortaTechAdapter createPay gateway timeout exception PaySchet.ID=' . $paySchet->ID);
            Yii::$app->queue
                ->delay(self::REFUND_REFRESH_STATUS_JOB_DELAY)
                ->push(new RefreshStatusPayJob([
                    'paySchetId' => $paySchet->ID,
                ]));

            Yii::$app->errorHandler->logException($e);
            throw $e;
        } catch (FortaServerException|FortaClientException|BankAdapterResponseException $e) {
            Yii::$app->errorHandler->logException($e);
            $createPayResponse->status = BaseResponse::STATUS_ERROR;
            $createPayResponse->message = $e->getMessage();
            return $createPayResponse;
        }

        if(!array_key_exists('url', $ans) || empty($ans['url'])) {
            throw new CreatePayException('FortaTechAdapter Empty 3ds url');
        }
        $createPayResponse = new CreatePayResponse();
        $createPayResponse->isNeed3DSRedirect = true;
        $createPayResponse->isNeed3DSVerif = false;
        $createPayResponse->status = BaseResponse::STATUS_DONE;
        $createPayResponse->transac = (string)$transId;
        $createPayResponse->url = $ans['url'];

        return $createPayResponse;
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
        // TODO: Implement reversOrder() method.
    }

    /**
     * @inheritDoc
     * @throws BankAdapterResponseException
     */
    public function checkStatusPay(OkPayForm $okPayForm)
    {
        return $this->getCommonStatusPay($okPayForm->getPaySchet());
    }

    /**
     * @param PaySchet $paySchet
     * @return CheckStatusPayResponse
     * @throws BankAdapterResponseException
     */
    protected function getCommonStatusPay(PaySchet $paySchet): CheckStatusPayResponse
    {
        /** @var CheckStatusPayResponse $checkStatusPayResponse */
        if(Yii::$app->cache->exists(self::REFUND_ID_CACHE_PREFIX . $paySchet->ID)) {
            $checkStatusPayResponse = $this->checkStatusPayRefund($paySchet);
        } elseif ($paySchet->uslugatovar->IsCustom == TU::$TOCARD) {
            $checkStatusPayResponse = $this->checkStatusPayOut($paySchet);
        } else {
            $checkStatusPayResponse = $this->checkStatusPayDefault($paySchet);
        }

        return $checkStatusPayResponse;
    }

    /**
     * @param PaySchet $paySchet
     * @return CheckStatusPayResponse
     * @throws BankAdapterResponseException
     */
    protected function checkStatusPayDefault(PaySchet $paySchet)
    {
        $ans = $this->sendGetStatusRequest($paySchet);

        $checkStatusPayResponse = new CheckStatusPayResponse();
        if(!array_key_exists('status', $ans) || empty($ans['status'])) {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = 'Ошибка проверки статуса';
            return $checkStatusPayResponse;
        }
        if(array_key_exists('error_description', $ans) && !empty($ans['error_description'])) {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = $ans['error_description'];
            $checkStatusPayResponse->rcCode = $this->parseRcCode($ans['error_description']);
            return $checkStatusPayResponse;
        }
        $checkStatusPayResponse->status = $this->convertStatus($ans['status']);
        $checkStatusPayResponse->message = $ans['status'];

        if($checkStatusPayResponse->status == BaseResponse::STATUS_DONE && array_key_exists('pay', $ans)) {
            $checkStatusPayResponse->operations = $ans['pay'];
        }
        return $checkStatusPayResponse;
    }

    /**
     * @param PaySchet $paySchet
     * @return CheckStatusPayResponse
     * @throws BankAdapterResponseException
     */
    protected function checkStatusPayOut(PaySchet $paySchet)
    {
        $ans = $this->sendGetStatusOutRequest($paySchet);

        $checkStatusPayResponse = new CheckStatusPayResponse();

        if($ans['status'] == true && isset($ans['data']['cards'][0]['transferParts'])) {
            // TODO: refact
            $transferParts = $ans['data']['cards'][0]['transferParts'];
            $errorData = '';
            $errorsCount = 0;
            $initCount = 0;
            $paidCount = 0;
            foreach ($transferParts as $transferPart) {
                if($transferPart['status'] == 'STATUS_ERROR') {
                    $errorData .= Json::encode($transferPart) . "\n";
                    $errorsCount++;
                } elseif ($transferPart['status'] == 'STATUS_INIT') {
                    $initCount++;
                } elseif ($transferPart['status'] == 'STATUS_PAID') {
                    $paidCount++;
                }
            }

            if(count($transferParts) === $paidCount) {
                // если все части платежей успешны
                $checkStatusPayResponse->status = BaseResponse::STATUS_DONE;
            } elseif (count($transferParts) === $errorsCount) {
                // если все части платежей с ошибкой
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            } elseif ($initCount === 0) {
                // если части платежей с ошибками и успешны
                $checkStatusPayResponse->status = BaseResponse::STATUS_CREATED;
                $checkStatusPayResponse->message = mb_substr($errorData, 0, 250);
            } else {
                $checkStatusPayResponse->status = BaseResponse::STATUS_CREATED;
            }
        } else {
            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = 'Ошибка запроса';
        }
        return $checkStatusPayResponse;
    }

    /**
     * @param PaySchet $paySchet
     * @return CheckStatusPayResponse
     */
    protected function checkStatusPayRefund(PaySchet $paySchet)
    {
        $refundIds = Yii::$app->cache->get(
            self::REFUND_ID_CACHE_PREFIX . $paySchet->ID
        );

        $checkStatusPayResponse = new CheckStatusPayResponse();
        $checkStatusPayResponse->status = BaseResponse::STATUS_CANCEL;
        $checkStatusPayResponse->message = 'Возврат';
        foreach ($refundIds as $refundId) {
            $ans = $this->sendGetStatusRefundRequest($refundId);
            Yii::warning('FortaTechAdapter checkStatusPayRefund: paySchet.ID=' . $paySchet->ID . ' ans=' . $ans);

            if($ans['status'] == 'STATUS_REFUND') {
                continue;
            } elseif ($ans['status'] == 'STATUS_INIT') {
                Yii::warning('FortaTechAdapter checkStatusPayRefund: queue refreshStatusPayJob ID=' . $paySchet->ID);
                Yii::$app->queue
                    ->delay(self::REFUND_REFRESH_STATUS_JOB_DELAY)
                    ->push(new RefreshStatusPayJob([
                        'paySchetId' => $paySchet->ID,
                    ]));
                break;
            } elseif ($ans['status'] == 'STATUS_ERROR' && isset($ans['message'])) {
                $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
                $checkStatusPayResponse->message = $ans['message'];
                break;
            } else {
                $checkStatusPayResponse->status = BaseResponse::STATUS_DONE;
                $checkStatusPayResponse->message = 'Возврат завершен с ошибкой';
                break;
            }
        }

        Yii::warning('FortaTechAdapter checkStatusPayRefund: paySchet.ID=' . $paySchet->ID
            . ' status=' . $checkStatusPayResponse->status
            . ' message=' . $checkStatusPayResponse->message
        );

        return $checkStatusPayResponse;
    }

    /**
     * @param string $cardNumber
     *
     * @return string
     * @throws CardTokenException
     */
    private function getCardToken(string $cardNumber): string
    {
        $queryUrl = '/api/cardToken?cardNumber='.$cardNumber;
        try {
            $cardData = $this->sendRequest($queryUrl, [], '', 'GET');
        } catch (FortaServerException|FortaClientException|BankAdapterResponseException $e) {
            throw new CardTokenException('Cant get card token. Card number: '.$this->maskCardNumber($cardNumber));
        }

        if (isset($cardData['status'], $cardData['data']['tokenExist']) && $cardData['status'] === true && $cardData['data']['tokenExist'] === true) {
            return $cardData['data']['token'];
        }
        throw new CardTokenException('Cant get card token. Card number: '.$this->maskCardNumber($cardNumber));
    }

    /**
     * @inheritDoc
     * @throws BankAdapterResponseException|CardTokenException
     */
    public function recurrentPay(AutoPayForm $autoPayForm): CreateRecurrentPayResponse
    {
        $action = '/api/recurrentPayment';
        $request = new RecurrentPayRequest();
        Yii::info([$action => $autoPayForm->attributes], 'recurentPay start');
        $request->orderId = (string)$autoPayForm->paySchet->ID;
        $request->amount = $autoPayForm->paySchet->getSummFull();
        $card = $autoPayForm->getCard();
        if (!$card) {
            throw new CardTokenException('cant get card');
        }
        $request->cardToken = $card->ExtCardIDP;
        $request->callbackUrl = $autoPayForm->paySchet->getCallbackUrl();

        try {
            $response = $this->sendRequest($action, $request->attributes, $this->buildRecurrentPaySignature($request));
        } catch (FortaServerException|FortaClientException|BankAdapterResponseException $e) {
            Yii::$app->errorHandler->logException($e);
            Yii::error([$e->getMessage(), $e->getTrace(), 'recurentPay send']);
            throw new BankAdapterResponseException('Ошибка запроса');
        } catch (FortaSignatureException $e) {
            Yii::$app->errorHandler->logException($e);
            throw $e;
        }

        $createRecurrentPayResponse = new CreateRecurrentPayResponse();
        $createRecurrentPayResponse->refreshStatusInterval = self::RECURRENT_REFRESH_STATUS_INTERVAL;

        if (isset($response['status'], $response['data']['paymentId']) && $response['status'] === true) {
            $createRecurrentPayResponse->status = BaseResponse::STATUS_DONE;
            $createRecurrentPayResponse->transac = $response['data']['paymentId'];

            return $createRecurrentPayResponse;
        }

        $createRecurrentPayResponse->status = BaseResponse::STATUS_ERROR;
        $createRecurrentPayResponse->message = $response['errors']['description'] ?? '';

        return $createRecurrentPayResponse;
    }

    /**
     * @param RecurrentPayRequest $recurrentPayRequest
     *
     * @return string
     * @throws FortaSignatureException
     */
    protected function buildRecurrentPaySignature(RecurrentPayRequest $recurrentPayRequest): string
    {
        $stringToSign = sprintf(
            '%s;%s;%s;',
            $recurrentPayRequest->orderId,
            $recurrentPayRequest->cardToken,
            $recurrentPayRequest->amount
        );

        $keyFilePath = \Yii::getAlias('@app/config/forta/' . str_replace('/', '', $this->gate->Login) . '.pem');
        try {
            return SignatureHelper::sign($stringToSign, file_get_contents($keyFilePath), OPENSSL_ALGO_SHA256);
        } catch (SignatureException $e) {
            \Yii::error('FortaTechAdapter signature error: ' . $e->getMessage());
            throw new FortaSignatureException('Не удалось создать подпись.');
        }
    }

    /**
     * @inheritDoc
     */
    public function refundPay(RefundPayForm $refundPayForm)
    {
        $action = '/api/refund';
        $refundPayResponse = new RefundPayResponse();
        try {
            $operations = Json::decode($refundPayForm->paySchet->Operations, true);
            foreach ($operations as $operation) {
                $refundPayRequest = new RefundPayRequest();
                $refundPayRequest->payment_id = $operation['payment_id'];

                try {
                    $ans = $this->sendRequest($action, $refundPayRequest->getAttributes());

                    if(array_key_exists('refund_id', $ans) && !empty($ans['refund_id'])) {
                        $refundIds = Yii::$app->cache->getOrSet(
                            self::REFUND_ID_CACHE_PREFIX . $refundPayForm->paySchet->ID,
                            function() {
                                return [];
                            }
                        );
                        $refundIds[] = $ans['refund_id'];
                        Yii::warning('FortaTechAdapter refundPay: add refundId=' . $ans['refund_id']
                                     . ' paySchet.ID=' . $refundPayForm->paySchet->ID
                        );

                        Yii::$app->cache->set(
                            self::REFUND_ID_CACHE_PREFIX . $refundPayForm->paySchet->ID,
                            $refundIds,
                            60 * 60 * 24 * 30
                        );
                        $refundPayResponse->status = BaseResponse::STATUS_CREATED;
                        $refundPayResponse->message = isset($ans['status']) ? $ans['status'] : '';
                    } else {
                        $refundPayResponse->status = BaseResponse::STATUS_ERROR;
                        $refundPayResponse->message = isset($ans['message']) ? $ans['message'] : 'Ошибка запроса';
                    }

                } catch (FortaServerException|FortaClientException|BankAdapterResponseException $e) {
                    Yii::$app->errorHandler->logException($e);
                    $refundPayResponse->status = BaseResponse::STATUS_ERROR;
                    $refundPayResponse->message = isset($ans['message']) ? $ans['message'] : 'Ошибка запроса';
                }
            }

            Yii::warning('FortaTechAdapter refundPay: queue refreshStatusPayJob ID=' . $refundPayForm->paySchet->ID);
            Yii::$app->queue
                ->delay(self::REFUND_REFRESH_STATUS_JOB_DELAY)
                ->push(new RefreshStatusPayJob([
                    'paySchetId' => $refundPayForm->paySchet->ID,
                ]));
        } catch (\Exception $e) {
            Yii::warning('FortaTechAdapter refundPay: paySchet.ID=' . $refundPayForm->paySchet->ID
                . ' exception=' . $e->getMessage()
            );

            $refundPayResponse->status = BaseResponse::STATUS_ERROR;
            $refundPayResponse->message = $e->getMessage();
        }

        Yii::warning('FortaTechAdapter refundPay: paySchet.ID='
            . $refundPayForm->paySchet->ID
            . ' status=' . $refundPayResponse->status
            . ' message=' . $refundPayResponse->message
        );

        return $refundPayResponse;
    }

    /**
     * @param string $errorMessage
     * @return string
     */
    protected function formatErrorMessage(string $errorMessage): string
    {
        $errorNeedles = array_keys($this->errorMatchList);
        foreach ($errorNeedles as $errorNeedle) {
            if (stripos($errorMessage, $errorNeedle) !== false) {
                return $this->errorMatchList[$errorNeedle];
            }
        }
        return $errorMessage;
    }

    /**
     * @inheritDoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm)
    {
        $action = '/api/transferToCardv2';
        $outCardPayRequest = new OutCardPayRequest();
        $outCardPayRequest->orderId = (string)$outCardPayForm->paySchet->ID;
        $outCardPayRequest->cards = [
            [
                'card' => (string)$outCardPayForm->cardnum,
                'amount' => $outCardPayForm->amount,
            ]
        ];

        $outCardPayResponse = new OutCardPayResponse();

        $signature = $this->buildSignatureByOutCardPay($outCardPayRequest);
        try {
            $ans = $this->sendRequest($action, $outCardPayRequest->getAttributes(), $signature);
        } catch (FortaGatewayTimeoutException $e) {
            Yii::error('FortaTechAdapter outCardPay gateway timeout exception paySchet.ID=' . $outCardPayForm->paySchet->ID);
            Yii::$app->queue
                ->delay(self::REFUND_REFRESH_STATUS_JOB_DELAY)
                ->push(new RefreshStatusPayJob([
                    'paySchetId' => $outCardPayForm->paySchet->ID,
                ]));

            Yii::$app->errorHandler->logException($e);
            throw $e;
        } catch (FortaServerException|FortaClientException|BankAdapterResponseException $e) {
            Yii::$app->errorHandler->logException($e);
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = $e->getMessage();
            return $outCardPayResponse;
        }

        if($ans['status'] == true && isset($ans['data']['id'])) {
            $outCardPayResponse->status = BaseResponse::STATUS_DONE;
            $outCardPayResponse->trans = $ans['data']['id'];
        } elseif ($ans['status'] == false && isset($ans['errors']['description'])) {
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = $ans['errors']['description'];
        } else {
            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;

            if(array_key_exists('errors', $ans) && isset($ans['errors'][0])) {
                $outCardPayResponse->message = $ans['errors'][0]['description'];
            } else {
                $outCardPayResponse->message = 'Ошибка запроса';
            }
        }

        if ($outCardPayResponse->message !== null) {
            $outCardPayResponse->message = $this->formatErrorMessage($outCardPayResponse->message);
        }
        return $outCardPayResponse;
    }

    /**
     * @param OutCardPayRequest $outCardPayRequest
     * @return string
     */
    protected function buildSignatureByOutCardPay(OutCardPayRequest $outCardPayRequest)
    {
        $s = sprintf(
            '%s;%s;%s;',
            $outCardPayRequest->orderId,
            $outCardPayRequest->cards[0]['card'],
            $outCardPayRequest->cards[0]['amount']
        );

        return $this->buildSignature($s);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function buildSignature(string $string): string
    {
        $keyFilePath = '@app/config/forta/' . escapeshellarg($this->gate->Login) . '.pem';
        $command = "echo -n " . escapeshellarg($string)
                   . " | openssl dgst -sha256 -sign " . Yii::getAlias($keyFilePath)
                   . " | openssl base64";

        return shell_exec($command);
    }

    /**
     * @inheritDoc
     */
    public function getAftMinSum()
    {
        return self::AFT_MIN_SUMM;
    }

    /**
     * @param $uri
     * @param $data
     * @param string $signature
     * @param string $methodType
     * @return array
     * @throws FortaServerException
     * @throws FortaClientException
     * @throws BankAdapterResponseException
     */
    protected function sendRequest($uri, $data, $signature = '', string $methodType = 'POST')
    {
        $requestJson = $data !== null ? Json::encode($data) : null;
        $maskedRequestString = $data !== null ? Json::encode($this->maskRequestCardInfo($data)) : null;

        \Yii::info([
            'message' => 'FortaTechAdapter request start.',
            'uri' => $uri,
            'method' => $methodType,
            'requestData' => $maskedRequestString,
            'signature' => $signature,
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Token: ' . $this->gate->Token,
            'User-Agent' => 'Vepay',
        ];
        if (!empty($signature)) {
            $headers['Signature'] = $signature;
        }
        $headers = array_map(function ($header){
            // remove new lines
            return trim(str_replace(["\r", "\n"], '', $header));
        }, $headers);

        $params = [
            'timeout' => 90,
            'headers' => $headers,
        ];
        if ($requestJson !== null) {
            $params['body'] = $requestJson;
        }

        $client = new \GuzzleHttp\Client();
        try {

            $response = $client->request($methodType, $this->bankUrl . $uri, $params);

        } catch (GuzzleException $e) {
            \Yii::$app->errorHandler->logException($e);
            if($e instanceof BadResponseException) {
                $statusCode = $e->getResponse()->getStatusCode();
                \Yii::error([
                    'message' => 'FortaTechAdapter bad response error.',
                    'uri' => $uri,
                    'method' => $methodType,
                    'requestData' => $maskedRequestString,
                    'responseStatusCode' => $statusCode,
                    'responseBody' => $e->getResponse()->getBody()->getContents(),
                    'signature' => $signature,
                ]);
                if ($e instanceof ServerException) {
                    throw new FortaServerException("Ошибка запроса", $statusCode);
                }
                if ($e instanceof ClientException) {
                    throw new FortaClientException("Ошибка запроса", $statusCode);
                }
            }

            \Yii::error([
                'message' => 'FortaTechAdapter HTTP error.',
                'uri' => $uri,
                'method' => $methodType,
                'requestData' => $maskedRequestString,
                'guzzleError' => $e->getMessage(),
            ]);
            throw new BankAdapterResponseException('Ошибка запроса');
        }

        $responseContent = $response->getBody()->getContents();

        \Yii::info([
            'message' => 'FortaTechAdapter request finish.',
            'uri' => $uri,
            'method' => $methodType,
            'requestData' => $maskedRequestString,
            'responseData' => $this->maskCardNumber($responseContent),
            'responseStatusCode' => $response->getStatusCode(),
        ]);

        return $this->parseResponse($responseContent);
    }

    /**
     * @param PaySchet $paySchet
     * @return array
     * @throws BankAdapterResponseException
     */
    protected function sendGetStatusRequest(PaySchet $paySchet)
    {
        $uri = "/api/payments?order_id={$paySchet->ID}";
        return $this->sendRequest($uri, null, '', 'GET');
    }

    protected function sendGetStatusOutRequest(PaySchet $paySchet)
    {
        $uri = "/api/transferToCardv2?orderId={$paySchet->ID}";
        return $this->sendRequest($uri, null, '', 'GET');
    }

    public function sendGetStatusRefundRequest($refundId)
    {
        $uri = "/api/refund?refund_id={$refundId}";
        return $this->sendRequest($uri, null, '', 'GET');
    }

    /**
     * @param string $status
     * @return int
     */
    protected function convertStatus(string $status)
    {
        switch($status) {
            case 'STATUS_HOLD':
            case 'STATUS_INIT':
            case 'STATUS_CONFIRMATION':
                return BaseResponse::STATUS_CREATED;
            case 'STATUS_PAID':
                return BaseResponse::STATUS_DONE;
            case 'STATUS_ERROR':
            default:
                return BaseResponse::STATUS_ERROR;
        }
    }

    /**
     * @param string $response
     * @return array
     */
    protected function parseResponse(string $response)
    {
        return Json::decode($response, true);
    }

    /**
     * @param GetBalanceRequest $getBalanceRequest
     * @return GetBalanceResponse
     * @throws BankAdapterResponseException
     */
    public function getBalance(
        GetBalanceRequest $getBalanceRequest
    ): GetBalanceResponse {
        $endpoint = $this->bankUrl . '/api/wallets';
        $type = $getBalanceRequest->accountType;
        $currency = $getBalanceRequest->currency;
        $getBalanceResponse = new GetBalanceResponse();
        $getBalanceResponse->bank_name = $getBalanceRequest->bankName;
        try {
            $response = $this->api->request(
                Client::METHOD_GET,
                $endpoint
            );
        } catch (GuzzleException $e) {
            Yii::warning('FortaTechAdapter getBalance exception: ' . $e->getMessage());

            throw new BankAdapterResponseException(
                BankAdapterResponseException::REQUEST_ERROR_MSG . ' : ' . $e->getMessage()
            );
        }

        Yii::warning('FortaTechAdapter getBalance: PartnerId=' . $this->gate->PartnerId
            . ' GateId=' . $this->gate->Id
            . ' Response=' . Json::encode($response->getBody())
        );

        if (!$response->isSuccess()) {
            $errorMsg = 'Balance service:: FortaTech request failed for type: ' . $type;
            throw new BankAdapterResponseException(
                BankAdapterResponseException::setErrorMsg($errorMsg)
            );
        }
        $responseData = $response->json('balance');
        $getBalanceResponse->amount = (float)$responseData[0]['availableBalance'];
        $getBalanceResponse->currency = $currency;
        $getBalanceResponse->account_type = $type;
        return $getBalanceResponse;
    }

    /**
     * @inheritDoc
     */
    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        // TODO: Implement transferToAccount() method.
    }

    public function identInit(Ident $ident)
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

    /**
     * @inheritDoc
     */
    public function identGetStatus(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        // TODO: Implement sendP2p() method.
    }

    /**
     * Парсит result code, rc приходит в errorDescription в квадратных скобках, например "[100] сообщение"
     *
     * @param string $errorDescription
     * @return string|null
     */
    protected function parseRcCode(string $errorDescription): ?string
    {
        if (preg_match('/\[(\d+)\]/', $errorDescription, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function registrationBenific(RegistrationBenificForm $registrationBenificForm)
    {
        throw new GateException('Метод недоступен');
    }
}
