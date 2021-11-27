<?php


namespace app\services\payment\banks;


use app\Api\Client\Client;
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
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\FortaBadRequestException;
use app\services\payment\exceptions\FortaForbiddenException;
use app\services\payment\exceptions\FortaGatewayTimeoutException;
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
use app\services\payment\helpers\TimeHelper;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\helpers\Json;

class FortaTechAdapter implements IBankAdapter
{
    const AFT_MIN_SUMM = 120000;
    const BANK_URL = 'https://pay1time.com';
    const BANK_URL_TEST = 'https://pay1time.com';

    const REFUND_ID_CACHE_PREFIX = 'Forta__RefundIds__';
    const REFUND_REFRESH_STATUS_JOB_DELAY = 30;

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
        } catch (FortaBadRequestException $e) {
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
     * @throws BankAdapterResponseException
     * @throws CardTokenException
     */
    private function getCardToken(string $cardNumber): string
    {
        $queryUrl = '/api/cardToken?cardNumber='.$cardNumber;
        $cardData = $this->sendRequest($queryUrl, [], '', 'GET');
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

        $request->orderId = $autoPayForm->paySchet->ID;
        $request->amount = $autoPayForm->paySchet->getSummFull();
        $card = $autoPayForm->getCard();
        if (!$card) {
            throw new CardTokenException('cant get card');
        }
        $request->cardToken = $this->getCardToken($card->CardNumber);
        $request->callbackUrl = $autoPayForm->postbackurl;

        try {
            $response = $this->sendRequest($action, $request->getAttributes(), $this->buildRecurrentPaySignature($request));
        } catch (BankAdapterResponseException $e) {
            Yii::error([$e->getMessage(), $e->getTrace(), 'recurentPay send']);
            throw new BankAdapterResponseException('Ошибка запроса');
        }

        $createRecurrentPayResponse = new CreateRecurrentPayResponse();

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
     */
    protected function buildRecurrentPaySignature(RecurrentPayRequest $recurrentPayRequest): string
    {
        $stringToEncode = sprintf(
            '%s;%s;%s;',
            $recurrentPayRequest->orderId,
            $recurrentPayRequest->cardToken,
            $recurrentPayRequest->amount
        );

        return $this->buildSignature($stringToEncode);
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
        } catch (FortaForbiddenException $e) {
            Yii::error([
                "FortaTechAdapter forbidden exception paySchet.ID={$outCardPayForm->paySchet->ID}",
                $e
            ]);

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
        $hash = hash('sha256', $string, true);
        $resPrivateKey = openssl_pkey_get_private(
            'file://' . Yii::getAlias('@app/config/forta/' . $this->gate->Login . '.pem')
        );
        $signature = null;
        openssl_private_encrypt($hash, $signature, $resPrivateKey);
        return base64_encode($signature);
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
     * @throws BankAdapterResponseException|FortaBadRequestException
     */
    protected function sendRequest($uri, $data, $signature = '', string $methodType = 'POST')
    {
        $curl = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Authorization: Token: ' . $this->gate->Token,
        ];

        if(!empty($signature)) {
            $headers[] = 'Signature: ' . $signature;
        }

        $curlOptions = [
            CURLOPT_URL => $this->bankUrl . $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $methodType,
            CURLOPT_POSTFIELDS => Json::encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ];
        curl_setopt_array($curl, $curlOptions);

        $maskedRequest = $this->maskRequestCardInfo($data);

        Yii::info(['curl to send' => $curlOptions], 'mfo/sendRequest');
        Yii::warning('FortaTechAdapter req uri=' . $uri .' : ' . Json::encode($maskedRequest));
        $response = curl_exec($curl);
        Yii::warning('FortaTechAdapter response:' . $response);
        $curlError = curl_error($curl);
        Yii::warning('FortaTechAdapter curlError:' . $curlError);
        $info = curl_getinfo($curl);

        // При ошибке 400 форта всегда возвращает ошибку строкой
        if ($info['http_code'] === 400) {
            Yii::error('FortaTechAdapter sendRequest 400 response: ' . $response);
            throw new FortaBadRequestException($response);
        }

        try {
            Yii::warning(sprintf(
                'FortaTechAdapter response: %s | curlError: %s | info: %s',
                $response,
                $curlError,
                Json::encode($info)
            ));
            $response = $this->parseResponse($response);
            $maskedResponse = $this->maskResponseCardInfo($response);
        } catch (\Exception $e) {
            Yii::$app->errorHandler->logException($e);
            throw new BankAdapterResponseException('Ошибка запроса');
        }

        if(empty($curlError) && ($info['http_code'] == 200 || $info['http_code'] == 201)) {
            Yii::warning('FortaTechAdapter ans uri=' . $uri .' : ' . Json::encode($maskedResponse));
            return $response;
        } elseif (isset($response['errors']['description'])) {
            Yii::error('FortaTechAdapter ans uri=' . $uri .' : ' . Json::encode($maskedResponse));
            return $response;
        } elseif ($response['result'] == false && isset($response['message'])) {
            Yii::error('FortaTechAdapter ans uri=' . $uri .' : ' . Json::encode($maskedResponse));
            return $response;
        } elseif ($info['http_code'] === 403) {
            Yii::error('FortaTechAdapter ans forbidden uri=' . $uri . ' : ' . Json::encode($maskedResponse));
            throw new FortaForbiddenException('Ошибка запроса');
        } elseif ($info['http_code'] === 504) {
            Yii::error('FortaTechAdapter gateway timeout exception');
            throw new FortaGatewayTimeoutException('Ошибка запроса: ' . $curlError);
        } else {
            Yii::error('FortaTechAdapter error uri=' . $uri .' status=' . $info['http_code']);
            throw new BankAdapterResponseException('Ошибка запроса: ' . $curlError);
        }
    }

    /**
     * @param PaySchet $paySchet
     * @return array
     * @throws BankAdapterResponseException
     */
    protected function sendGetStatusRequest(PaySchet $paySchet)
    {
        $curl = curl_init();

        $url = sprintf(
            '%s/api/payments?order_id=%s&id=%s',
            $this->bankUrl,
            $paySchet->ID,
            $paySchet->ExtBillNumber
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Token: ' . $this->gate->Token,
            ),
        ));

        Yii::warning('FortaTechAdapter req uri=' . $url);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);

        if(empty($curlError) && ($info['http_code'] == 200 || $info['http_code'] == 201)) {
            $response = $this->parseResponse($response);
            $maskedResponse = $this->maskResponseCardInfo($response);
            Yii::warning('FortaTechAdapter ans uri=' . $url .' : ' . Json::encode($maskedResponse));
            return $response;
        } else {
            Yii::error('FortaTechAdapter error uri=' . $url .' status=' . $info['http_code']);
            throw new BankAdapterResponseException('Ошибка запроса: ' . $curlError);
        }
    }

    protected function sendGetStatusOutRequest(PaySchet $paySchet)
    {
        $curl = curl_init();

        $url = sprintf(
            '%s/api/transferToCardv2?orderId=%s',
            $this->bankUrl,
            $paySchet->ID
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Token: ' . $this->gate->Token,
            ),
        ));

        Yii::warning('FortaTechAdapter req uri=' . $url);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);

        if(empty($curlError) && ($info['http_code'] == 200 || $info['http_code'] == 201)) {
            $response = $this->parseResponse($response);
            $maskedResponse = $this->maskResponseCardInfo($response);
            Yii::warning('FortaTechAdapter ans uri=' . $url .' : ' . Json::encode($maskedResponse));
            return $response;
        } else {
            Yii::error('FortaTechAdapter error uri=' . $url .' status=' . $info['http_code']);
            throw new BankAdapterResponseException('Ошибка запроса: ' . $curlError);
        }
    }

    public function sendGetStatusRefundRequest($refundId)
    {
        $curl = curl_init();

        $url = sprintf(
            '%s/api/refund?refund_id=%s',
            $this->bankUrl,
            $refundId
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Token: ' . $this->gate->Token,
            ),
        ));

        Yii::warning('FortaTechAdapter req uri=' . $url);
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $info = curl_getinfo($curl);

        if (empty($curlError) && ($info['http_code'] == 200 || $info['http_code'] == 201)) {
            $response = $this->parseResponse($response);
            $maskedResponse = $this->maskResponseCardInfo($response);
            Yii::warning('FortaTechAdapter ans uri=' . $url . ' : ' . Json::encode($maskedResponse));
            return $response;
        } else {
            Yii::error('FortaTechAdapter error uri=' . $url . ' status=' . $info['http_code']);
        }
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

    private function maskRequestCardInfo(array $data): array
    {
        // CreatePayRequest model
        if (isset($data['cardNumber'])) {
            $data['cardNumber'] = $this->maskCardNumber($data['cardNumber']);
        }

        // CreatePayRequest model
        if (isset($data['cvv'])) {
            $data['cvv'] = '***';
        }

        // OutCardPayRequest model
        if (isset($data['cards']) && is_array($data['cards'])) {
            foreach ($data['cards'] as &$card) {
                $card['card'] = $this->maskCardNumber($card['card']);
            }
        }

        return $data;
    }

    private function maskResponseCardInfo(array $response): array
    {
        if (isset($response['data']) && isset($response['data']['cards'])) {
            foreach ($response['data']['cards'] as &$card) {
                $card['card'] = $this->maskCardNumber($card['card']);
            }
        }

        return $response;
    }

    private function maskCardNumber(string $cardNumber): string
    {
        return preg_replace('/(\d{6})(.+)(\d{4})/', '$1****$3', $cardNumber);
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
}
