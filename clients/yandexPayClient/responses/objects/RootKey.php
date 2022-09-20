<?php

namespace app\clients\yandexPayClient\responses\objects;

use yii\base\BaseObject;

class RootKey extends BaseObject
{
    /**
     * @var string
     */
    private $protocolVersion;

    /**
     * @var string base64 encoded keyValue
     */
    private $keyValue;

    /**
     * @var string key expiration time in unix timestamp ms
     */
    private $keyExpiration;

    /**
     * @param string $protocolVersion
     * @param string $keyValue
     * @param string $keyExpiration
     */
    public function __construct(string $protocolVersion, string $keyValue, string $keyExpiration)
    {
        parent::__construct();

        $this->protocolVersion = $protocolVersion;
        $this->keyValue = $keyValue;
        $this->keyExpiration = $keyExpiration;
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
    public function getKeyValue(): string
    {
        return $this->keyValue;
    }

    /**
     * @return int
     */
    public function getKeyExpiration(): int
    {
        return (int)$this->keyExpiration;
    }
}
