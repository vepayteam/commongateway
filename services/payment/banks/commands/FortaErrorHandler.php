<?php

namespace app\services\payment\banks\commands;

use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\FortaBadRequestException;
use app\services\payment\exceptions\FortaDisabledRecurrentException;
use app\services\payment\exceptions\FortaForbiddenException;
use app\services\payment\exceptions\FortaGatewayTimeoutException;
use app\services\payment\exceptions\FortaNotFoundException;
use app\services\payment\exceptions\FortaUnauthorizedException;
use app\services\payment\traits\MaskableTrait;
use Yii;
use yii\helpers\Json;

class FortaErrorHandler
{
    use MaskableTrait;

    public $uri;
    public $curl;
    public $response;

    /**
     * @param string $uri
     * @param resource $curl
     * @param string $response
     */
    public function __construct(string $uri, $curl, string $response)
    {
        $this->uri = $uri;
        $this->response = $response;
        $this->curl = $curl;
    }

    /**
     * @throws FortaBadRequestException
     * @throws BankAdapterResponseException
     * @throws FortaUnauthorizedException
     * @throws FortaNotFoundException
     */
    public function exec(): void
    {
        $response = is_array($this->response) ? Json::encode($this->maskResponseCardInfo($this->response)) : $this->response;
        try {
            $responseDecoded = Json::decode($response);
        } catch (\yii\base\InvalidArgumentException $e) {
            $responseDecoded = null;
        }

        Yii::warning('FortaTechAdapter response:'.$response);
        $curlError = curl_error($this->curl);
        Yii::warning('FortaTechAdapter curlError:'.$curlError);
        $info = curl_getinfo($this->curl);

        Yii::warning(sprintf(
            'FortaTechAdapter response: %s | curlError: %s | info: %s',
            $response,
            $curlError,
            Json::encode($info)
        ));

        // - обработка предупреждений
        if(empty($curlError) && ($info['http_code'] == 200 || $info['http_code'] == 201)) {
            Yii::warning('FortaTechAdapter ans uri=' . $this->uri .' : ' . $response);
            return;
        }

        if (isset($responseDecoded['errors']['description'])) {
            Yii::error('FortaTechAdapter ans uri=' . $this->uri .' : ' . $response);
            return;
        }

        if ($responseDecoded && $responseDecoded['result'] == false && isset($responseDecoded['message'])) {
            Yii::error('FortaTechAdapter ans uri=' . $this->uri .' : ' . $response);
            return;
        }

        // - обработка ошибок
        // При ошибке 400/500 (500 - при ошибках валидации со стороны Форты почему-то 500 http-статус)
        // форта всегда возвращает ошибку строкой
        if (in_array($info['http_code'], [400, 500], true)) {
            Yii::error('FortaTechAdapter sendRequest 400/500 response: '.$response);
            throw new FortaBadRequestException($this->response);
        }

        if ($info['http_code'] === 401) {
            Yii::error('FortaTechAdapter ans unauthorized uri='.$this->uri.' : '.$response);
            throw new FortaUnauthorizedException('Ошибка запроса');
        }

        if ($info['http_code'] === 403) {
            Yii::error('FortaTechAdapter ans forbidden uri='.$this->uri.' : '.$response);
            throw new FortaForbiddenException('Ошибка запроса');
        }

        if ($info['http_code'] === 404) {
            Yii::error('FortaTechAdapter ans not found uri='.$this->uri.' : '.$response);
            throw new FortaNotFoundException('Ошибка запроса');
        }

        if ($info['http_code'] === 405) {
            Yii::error('FortaTechAdapter ans disabled recurring payments uri='.$this->uri.' : '.$response);
            throw new FortaDisabledRecurrentException('Ошибка запроса');
        }

        if ($info['http_code'] === 411) {
            Yii::error('FortaTechAdapter ans 411 uri='.$this->uri.' : '.$response);
            throw new FortaBadRequestException('Ошибка запроса');
        }

        if ($info['http_code'] === 504) {
            Yii::error('FortaTechAdapter gateway timeout exception');
            throw new FortaGatewayTimeoutException('Ошибка запроса: '.$curlError);
        }

        Yii::error('FortaTechAdapter error uri='.$this->uri.' status='.$info['http_code']);
        throw new BankAdapterResponseException('Ошибка запроса: '.$curlError);
    }
}
