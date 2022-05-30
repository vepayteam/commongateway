<?php

namespace app\services\yandexPay\models;

use yii\base\Model;

class SignedMessage extends Model
{
    /**
     * @var string base64 encoded encryptedMessage
     */
    public $encryptedMessage;

    /**
     * @var string base64 encoded tag
     */
    public $tag;

    /**
     * @var string base64 encoded ephemeralPublicKey
     */
    public $ephemeralPublicKey;

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
    public function getRawEncryptedMessage(): string
    {
        return $this->encryptedMessage;
    }

    /**
     * @return string
     */
    public function getRawTag(): string
    {
        return $this->tag;
    }

    /**
     * @return string
     */
    public function getRawEphemeralPublicKey(): string
    {
        return $this->ephemeralPublicKey;
    }

    /**
     * @return string
     */
    public function getDecodedEncryptedMessage(): string
    {
        return base64_decode($this->encryptedMessage);
    }

    /**
     * @return string
     */
    public function getDecodedTag(): string
    {
        return base64_decode($this->tag);
    }

    /**
     * @return string
     */
    public function getDecodedEphemeralPublicKey(): string
    {
        return base64_decode($this->ephemeralPublicKey);
    }
}
