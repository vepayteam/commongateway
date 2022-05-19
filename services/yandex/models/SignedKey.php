<?php

namespace app\services\yandex\models;

use yii\base\Model;

class SignedKey extends Model
{
    /**
     * @var string base64 encoded keyValue
     */
    public $keyValue;

    /**
     * @var string key expiration time in unix timestamp ms
     */
    public $keyExpiration;

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
    public function getRawKeyValue(): string
    {
        return $this->keyValue;
    }

    /**
     * @return string
     */
    public function getDecodedKeyValue(): string
    {
        return base64_decode($this->keyValue);
    }

    /**
     * @return int
     */
    public function getKeyExpiration(): int
    {
        return (int)$this->keyExpiration;
    }
}
