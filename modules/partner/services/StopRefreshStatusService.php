<?php

namespace app\modules\partner\services;

use Yii;

/**
 * Сервис для сохранения в кэш id операций для которых нужно остановить опрос статуса
 */
class StopRefreshStatusService
{
    private const CACHE_KEY_PREFIX = 'StopRefreshStatusService_';
    private const CACHE_DURATION = 3600; // 1 hour

    /**
     * @var int
     */
    private $paySchetId;

    /**
     * @param int $paySchetId
     */
    public function __construct(int $paySchetId)
    {
        $this->paySchetId = $paySchetId;
    }

    /**
     * @return void
     */
    public function addStopRefreshStatus()
    {
        Yii::$app->cache->set($this->getCacheKey(), 'ok', self::CACHE_DURATION);
    }

    /**
     * @return bool
     */
    public function isStopRefreshStatus(): bool
    {
        return Yii::$app->cache->get($this->getCacheKey());
    }

    /**
     * @return string
     */
    private function getCacheKey(): string
    {
        return self::CACHE_KEY_PREFIX . $this->paySchetId;
    }
}
