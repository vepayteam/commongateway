<?php

namespace app\services\payment\banks;

use app\Api\Client\Client;
use app\services\ident\models\Ident;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\bank_adapter_responses\GetBalanceResponse;
use app\services\payment\banks\bank_adapter_responses\OutCardPayResponse;
use app\services\payment\banks\data\ClientData;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\PayloniumServerException;
use app\services\payment\exceptions\PayloniumTransactionNotFound;
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
use app\services\payment\models\PartnerBankGate;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class PayloniumAdapter implements IBankAdapter
{
    /**
     * Все запросы шлются на один endpoint в конце нужно добавить username
     * Полный url выглядит так https://api.paylonium.com/api/v1/payment-easy/username
     */
    private const BASE_URL_BANK = 'https://api.paylonium.com/api/v1/payment-easy/';

    /**
     * @var int
     */
    public static $bank = 16;

    /**
     * @var string
     */
    private $bankUrl;

    /**
     * @var PartnerBankGate
     */
    private $gate;

    /**
     * @var Client
     */
    private $api;

    public function setGate(PartnerBankGate $partnerBankGate)
    {
        $this->gate = $partnerBankGate;

        $this->bankUrl = self::BASE_URL_BANK . $partnerBankGate->Login;

        $infoMessage = sprintf(
            'partnerId=%d bankId=%d',
            $this->gate->PartnerId,
            $this->getBankId()
        );

        /**
         * Существует проблема, что при попытке сделать запрос через curl ругается на их сертификат, который через браузер валидный
         * В поддержке посоветовали отключить верификацию
         */
        $this->api = new Client([
            'verify' => false,
        ], $infoMessage);
    }

    /**
     * @inheritdoc
     */
    public function getBankId(): int
    {
        return self::$bank;
    }

    public function confirm(DonePayForm $donePayForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function createPay(CreatePayForm $createPayForm, ClientData $clientData)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function checkStatusPay(OkPayForm $okPayForm): CheckStatusPayResponse
    {
        $checkStatusPayResponse = new CheckStatusPayResponse();

        $xmlRequest = $this->getCommonRequestXmlElement();
        $xmlRequest->addChild('status');
        $xmlRequest->status->addAttribute('id', $okPayForm->IdPay);

        try {
            $xmlResponse = $this->sendRequest($xmlRequest);
            $responseData = $this->parseTransactionResponse($xmlResponse);
        } catch (PayloniumServerException $e) {
            \Yii::warning(['PayloniumAdapter checkStatusPay', $e]);

            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = BankAdapterResponseException::REQUEST_ERROR_MSG;

            return $checkStatusPayResponse;
        }

        try {
            $status = $this->getTransactionBaseResponseStatus($responseData);
        } catch (PayloniumTransactionNotFound $e) {
            \Yii::$app->errorHandler->logException($e);

            $checkStatusPayResponse->status = BaseResponse::STATUS_ERROR;
            $checkStatusPayResponse->message = 'Платеж не найден';

            return $checkStatusPayResponse;
        }

        $checkStatusPayResponse->status = $status;
        switch ($status) {
            case BaseResponse::STATUS_CREATED:
                $checkStatusPayResponse->message = 'Ожидается запрос статуса';
                break;
            case BaseResponse::STATUS_DONE:
                $checkStatusPayResponse->transId = $responseData['trans'];
                break;
            case BaseResponse::STATUS_ERROR:
                $checkStatusPayResponse->message = $responseData['additional']['error-description'] ?? BankAdapterResponseException::REQUEST_ERROR_MSG;
                break;
            default:
                break;
        }

        return $checkStatusPayResponse;
    }

    public function recurrentPay(AutoPayForm $autoPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function refundPay(RefundPayForm $refundPayForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function outCardPay(OutCardPayForm $outCardPayForm): OutCardPayResponse
    {
        $outCardPayResponse = new OutCardPayResponse();

        /**
         * Для параметра service у paylonium поддерживается 2 значения 24 и 25
         *
         * 24 - вывод на карты
         * 25 - вывод на qiwi кошельки
         *
         * в нашем случае есть лишь вывод на карты
         */
        $xmlRequest = $this->getCommonRequestXmlElement();
        $xmlRequest->addChild('payment');
        $xmlRequest->payment->addAttribute('id', $outCardPayForm->paySchet->ID);
        $xmlRequest->payment->addAttribute('sum', $outCardPayForm->amount);
        $xmlRequest->payment->addAttribute('service', '24');
        $xmlRequest->payment->addAttribute('account', $outCardPayForm->cardnum);
        $xmlRequest->payment->addAttribute('date', date('c', $outCardPayForm->paySchet->DateCreate)); // Дата создания платежа в формате стандарта ISO 8601

        if ($outCardPayForm->phone) {
            $xmlRequest->payment->addChild('attribute');
            $xmlRequest->payment->attribute->addAttribute('name', 'phone');
            $xmlRequest->payment->attribute->addAttribute('value', $outCardPayForm->phone);
        }

        try {
            $xmlResponse = $this->sendRequest($xmlRequest);
            $responseData = $this->parseTransactionResponse($xmlResponse);
        } catch (PayloniumServerException $e) {
            \Yii::warning(['PayloniumAdapter outCardPay', $e]);

            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = $e->getMessage();

            return $outCardPayResponse;
        }

        try {
            $status = $this->getTransactionBaseResponseStatus($responseData);
        } catch (PayloniumTransactionNotFound $e) {
            \Yii::$app->errorHandler->logException($e);

            $outCardPayResponse->status = BaseResponse::STATUS_ERROR;
            $outCardPayResponse->message = 'Платеж не найден';

            return $outCardPayResponse;
        }

        $outCardPayResponse->status = $status;
        switch ($status) {
            case BaseResponse::STATUS_CREATED:
                $outCardPayResponse->message = 'Ожидается запрос статуса';
                break;
            case BaseResponse::STATUS_DONE:
                $outCardPayResponse->trans = $responseData['trans'];
                break;
            case BaseResponse::STATUS_ERROR:
                $outCardPayResponse->message = $responseData['additional']['error-description'] ?? BankAdapterResponseException::REQUEST_ERROR_MSG;
                break;
            default:
                break;
        }

        return $outCardPayResponse;
    }

    public function getAftMinSum()
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * @inheritdoc
     */
    public function getBalance(GetBalanceRequest $getBalanceRequest): GetBalanceResponse
    {
        $getBalanceResponse = new GetBalanceResponse();
        $getBalanceResponse->bank_name = $getBalanceRequest->bankName;
        $getBalanceResponse->currency = $getBalanceRequest->currency;
        $getBalanceResponse->account_type = $getBalanceRequest->accountType;

        $xmlRequest = $this->getCommonRequestXmlElement();
        $xmlRequest->addChild('balance');

        try {
            $xmlResponse = $this->sendRequest($xmlRequest);
        } catch (PayloniumServerException $e) {
            \Yii::$app->errorHandler->logException($e);

            throw new BankAdapterResponseException(BankAdapterResponseException::REQUEST_ERROR_MSG);
        }

        $balanceAmount = (float)current($xmlResponse->balance['balance']);
        $getBalanceResponse->amount = PaymentHelper::convertToFullAmount($balanceAmount);

        return $getBalanceResponse;
    }

    public function transferToAccount(OutPayAccountForm $outPayaccForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function identInit(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    public function identGetStatus(Ident $ident)
    {
        throw new GateException('Метод недоступен');
    }

    public function currencyExchangeRates()
    {
        throw new GateException('Метод недоступен');
    }

    public function sendP2p(SendP2pForm $sendP2pForm)
    {
        throw new GateException('Метод недоступен');
    }

    public function registrationBenific(RegistrationBenificForm $registrationBenificForm)
    {
        throw new GateException('Метод недоступен');
    }

    /**
     * Для методов outCardPay и checkStatusPay возвращается один и тот же ответ
     *
     * Результат выполнения операции возвращается по трем параметрам, цитата из документации:
     * "Значения "final", "code", "state" используются для интерпретации результата выполнения платежа.
     * Фактически принимайте решения в вашем алгоритме по следующим сочетаниям значений"
     *
     * В случае если по трем параметрам не было найдено соответствия,
     * то следует считать операцию незаконченной и проверить статус позже
     *
     * Цитата из документации
     * "Если попадутся сочетания значений, которых нет в списке выше, то никак их не интерпретируйте,
     * игнорируйте, считайте, что ответ от сервера не получен."
     *
     *
     * @param array $transactionResponse
     * @return int
     * @throws PayloniumTransactionNotFound
     */
    private function getTransactionBaseResponseStatus(array $transactionResponse): int
    {
        if (
            (int)$transactionResponse['code'] === 0 &&
            (int)$transactionResponse['state'] === 60 &&
            (int)$transactionResponse['final'] === 1
        ) {
            return BaseResponse::STATUS_DONE;
        } else if (
            (int)$transactionResponse['code'] === 20 &&
            (int)$transactionResponse['state'] === 80 &&
            (int)$transactionResponse['final'] === 1
        ) {
            return BaseResponse::STATUS_ERROR;
        } else if (
            (int)$transactionResponse['code'] === 15 &&
            (int)$transactionResponse['state'] === -2 &&
            (int)$transactionResponse['final'] === 1
        ) {
            throw new PayloniumTransactionNotFound();
        }

        return BaseResponse::STATUS_CREATED;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return array
     */
    private function parseTransactionResponse(\SimpleXMLElement $xml): array
    {
        $commonAttributes = current($xml->result->attributes());
        $additionalAttributes = [];

        foreach ($xml->result->children() as $child) {
            if ($child->getName() !== 'attribute') {
                continue;
            }

            $name = current($child['name']);
            $value = current($child['value']);

            $additionalAttributes[$name] = $value;
        }

        $commonAttributes['additional'] = $additionalAttributes;

        return $commonAttributes;
    }

    /**
     * @param \SimpleXMLElement $xmlRequest
     * @return \SimpleXMLElement
     * @throws PayloniumServerException
     */
    private function sendRequest(\SimpleXMLElement $xmlRequest): \SimpleXMLElement
    {
        $requestData = $this->convertXmlElementToString($xmlRequest);
        $signature = $this->getSignature($requestData);

        try {
            $response = $this->api->getClient()->post($this->bankUrl, [
                RequestOptions::BODY => $requestData,
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/xml',
                    'Signature' => $signature,
                ],
            ]);
        } catch (GuzzleException $e) {
            \Yii::$app->errorHandler->logException($e);
            \Yii::error([
                'message' => 'PayloniumAdapter send request error',
                'url', $this->bankUrl,
                'requestData' => $requestData,
                'signature' => $signature,
            ]);

            throw new PayloniumServerException(BankAdapterResponseException::REQUEST_ERROR_MSG);
        }

        $content = (string)$response->getBody();

        $xmlResponse = new \SimpleXMLElement($content);
        if ($xmlResponse->getName() === 'error') {
            $message = (string)$xmlResponse;

            throw new PayloniumServerException($message);
        }

        return $xmlResponse;
    }

    /**
     * @return \SimpleXMLElement
     */
    private function getCommonRequestXmlElement(): \SimpleXMLElement
    {
        return new \SimpleXMLElement('<request></request>');
    }

    /**
     * Функция конвертации \SimpleXMLElement в строку
     * Если использовать встроенную функцию $xml->saveXML(), то добавится ненужная шапка <?xml version="1.0"?>
     *
     * @param \SimpleXMLElement $xml
     * @return string
     */
    private function convertXmlElementToString(\SimpleXMLElement $xml): string
    {
        $dom = dom_import_simplexml($xml);
        return $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
    }

    /**
     * @param string $requestData xml request string
     * @return string request signature
     */
    private function getSignature(string $requestData): string
    {
        $pathPrivateKey = \Yii::getAlias('@app/config/paylonium/' . $this->gate->AdvParam_1);

        $privateKey = openssl_get_privatekey(file_get_contents($pathPrivateKey));
        openssl_sign($requestData, $signature, $privateKey);
        openssl_free_key($privateKey);

        return base64_encode($signature);
    }
}
