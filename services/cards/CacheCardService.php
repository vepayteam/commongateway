<?php

namespace app\services\cards;

use Yii;

class CacheCardService
{
    private const CACHE_KEY_PREFIX = 'CacheCardService_';
    private const CACHE_DURATION = 1800; // 30 min

    /** @var int */
    private $paySchetId;

    /**
     * @param int $paySchetId
     */
    public function __construct(int $paySchetId)
    {
        $this->paySchetId = $paySchetId;
    }


    /**
     * @param string $card
     */
    public function setCard(string $card)
    {
        Yii::$app->cache->set($this->getCacheKey(), $card, self::CACHE_DURATION);
    }

    /**
     * @return bool
     */
    public function cardExists(): bool
    {
        return Yii::$app->cache->exists($this->getCacheKey());
    }

    /**
     * @return string
     */
    public function getCard(): string
    {
        return Yii::$app->cache->get($this->getCacheKey());
    }

    public function deleteCard()
    {
        Yii::$app->cache->delete($this->getCacheKey());
    }

    /**
     * @return string
     */
    private function getCacheKey(): string
    {
        return self::CACHE_KEY_PREFIX . $this->paySchetId;
    }
}
