<?php

namespace app\clients;

use app\Api\Client\Client;
use app\clients\payloniumClient\requests\BaseRequest;
use app\clients\payloniumClient\requests\GetStatusRequest;
use app\clients\payloniumClient\requests\BalanceRequest;
use app\clients\payloniumClient\requests\OutCardPayRequest;
use app\clients\payloniumClient\responses\BalanceResponse;
use app\clients\payloniumClient\responses\TransactionStatusResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\PayloniumServerException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use yii\base\BaseObject;

class PayloniumClient extends BaseObject
{
    /**
     * @var string
     */
    private $bankUrl;

    /**
     * @var string
     */
    private $privateKeyPath;

    /**
     * @var Client
     */
    private $api;

    public function __construct(string $bankUrl, string $privateKeyPath, int $partnerId, int $bankId)
    {
        parent::__construct();

        $this->bankUrl = $bankUrl;
        $this->privateKeyPath = $privateKeyPath;

        /**
         * Существует проблема, что при попытке сделать запрос через curl ругается на их сертификат, который через браузер валидный
         * В поддержке посоветовали отключить верификацию
         */
        $infoMessage = sprintf('partnerId=%d bankId=%d', $partnerId, $bankId);
        $this->api = new Client([
            'verify' => false,
        ], $infoMessage);
    }

    /**
     * @param OutCardPayRequest $outCardPayRequest
     * @return TransactionStatusResponse
     * @throws PayloniumServerException
     */
    public function outCardPay(OutCardPayRequest $outCardPayRequest): TransactionStatusResponse
    {
        $xmlResponse = $this->sendRequest($outCardPayRequest);

        return $this->getTransactionStatusResponse($xmlResponse);
    }

    /**
     * @param GetStatusRequest $getStatusRequest
     * @return TransactionStatusResponse
     * @throws PayloniumServerException
     */
    public function checkStatusPay(GetStatusRequest $getStatusRequest): TransactionStatusResponse
    {
        $xmlResponse = $this->sendRequest($getStatusRequest);

        return $this->getTransactionStatusResponse($xmlResponse);
    }

    /**
     * @param BalanceRequest $balanceRequest
     * @return BalanceResponse
     * @throws PayloniumServerException
     */
    public function getBalance(BalanceRequest $balanceRequest): BalanceResponse
    {
        $xmlResponse = $this->sendRequest($balanceRequest);

        return new BalanceResponse((float)current($xmlResponse->balance['balance']));
    }

    /**
     * @param \SimpleXMLElement $xmlResponse
     * @return TransactionStatusResponse
     */
    private function getTransactionStatusResponse(\SimpleXMLElement $xmlResponse): TransactionStatusResponse
    {
        $commonAttributes = current($xmlResponse->result->attributes());

        $additionalAttributes = [];
        foreach ($xmlResponse->result->children() as $child) {
            if ($child->getName() !== 'attribute') {
                continue;
            }

            $name = current($child['name']);
            $value = current($child['value']);

            $additionalAttributes[$name] = $value;
        }

        return new TransactionStatusResponse(
            $commonAttributes['id'],
            $commonAttributes['code'],
            $commonAttributes['state'],
            $commonAttributes['final'],
            $commonAttributes['trans'] ?? null,
            $additionalAttributes['fee'] ?? null,
            $additionalAttributes['error-description'] ?? null
        );
    }

    /**
     * @param BaseRequest $request
     * @return \SimpleXMLElement
     * @throws PayloniumServerException
     */
    private function sendRequest(BaseRequest $request): \SimpleXMLElement
    {
        $requestData = $request->toRequestString();
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
                'message' => 'PayloniumClient send request error',
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
     * @param string $requestData xml request string
     * @return string request signature
     */
    private function getSignature(string $requestData): string
    {
        $privateKey = openssl_get_privatekey(file_get_contents($this->privateKeyPath));
        openssl_sign($requestData, $signature, $privateKey);
        openssl_free_key($privateKey);

        return base64_encode($signature);
    }
}