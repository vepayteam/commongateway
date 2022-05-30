<?php

namespace app\services\yandexPay\models;

use yii\base\Model;
use yii\helpers\Json;

class PaymentToken extends Model
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $signedMessage;

    /**
     * @var string
     */
    public $protocolVersion;

    /**
     * @var string base64 encoded signature
     */
    public $signature;

    /**
     * @var array
     */
    public $intermediateSigningKey;

    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return SignedMessage
     */
    public function getSignedMessage(): SignedMessage
    {
        return new SignedMessage(Json::decode($this->signedMessage));
    }

    /**
     * @return string
     */
    public function getJsonSignedMessage(): string
    {
        return $this->signedMessage;
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @return string
     */
    public function getRawSignature(): string
    {
        return $this->signature;
    }

    /**
     * @return string
     */
    public function getDecodedSignature(): string
    {
        return base64_decode($this->signature);
    }

    /**
     * @return IntermediateSigningKey
     */
    public function getIntermediateSigningKey(): IntermediateSigningKey
    {
        return new IntermediateSigningKey($this->intermediateSigningKey);
    }
}
