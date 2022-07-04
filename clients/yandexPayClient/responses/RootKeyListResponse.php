<?php

namespace app\clients\yandexPayClient\responses;

use app\clients\yandexPayClient\responses\objects\RootKey;
use yii\base\BaseObject;

class RootKeyListResponse extends BaseObject
{
    /**
     * @var RootKey[]
     */
    private $keys;

    /**
     * @param RootKey[] $keys список root ключей для верификации payment token
     */
    public function __construct(array $keys)
    {
        parent::__construct();

        $this->keys = $keys;
    }

    /**
     * @return RootKey[]
     */
    public function getKeys(): array
    {
        return $this->keys;
    }
}
