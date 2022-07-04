<?php

namespace app\services\yandexPay\models;

use yii\base\Model;
use yii\helpers\Json;

class IntermediateSigningKey extends Model
{
    /**
     * @var string
     */
    public $signedKey;

    /**
     * @var string[]
     */
    public $signatures;

    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * @return SignedKey
     */
    public function getSignedKey(): SignedKey
    {
        return new SignedKey(Json::decode($this->signedKey));
    }

    /**
     * @return string
     */
    public function getJsonSignedKey(): string
    {
        return $this->signedKey;
    }

    /**
     * @return string[]
     */
    public function getRawSignatures(): array
    {
        return $this->signatures;
    }

    /**
     * @return string[]
     */
    public function getDecodedSignatures(): array
    {
        return array_map(function (string $element) {
            return base64_decode($element);
        }, $this->signatures);
    }
}
