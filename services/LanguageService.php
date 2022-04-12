<?php

namespace app\services;

use yii\base\Component;

class LanguageService extends Component
{
    private const CACHE_KEY = 'LanguageService.paySchetId.';

    const API_LANG_RUS = 'rus';
    const API_LANG_ENG = 'eng';

    const APP_LANG_RUS = 'ru-RU';
    const APP_LANG_ENG = 'en-US';

    const ALL_API_LANG_LIST = [self::API_LANG_RUS, self::API_LANG_ENG];

    private $apiLangToAppLang = [
        self::API_LANG_RUS => self::APP_LANG_RUS,
        self::API_LANG_ENG => self::APP_LANG_ENG,
    ];

    public function saveApiLanguage(int $paySchetId, string $apiLanguage)
    {
        \Yii::$app->cache->set($this->getCacheKey($paySchetId), $apiLanguage);
    }

    public function setAppLanguage(int $paySchetId)
    {
        $apiLanguage = \Yii::$app->cache->get($this->getCacheKey($paySchetId));
        if ($apiLanguage && array_key_exists($apiLanguage, $this->apiLangToAppLang)) {
            \Yii::$app->language = $this->apiLangToAppLang[$apiLanguage];
        } else {
            \Yii::$app->language = $this->apiLangToAppLang[self::API_LANG_RUS];
        }
    }

    public function getAppLanguage(): string
    {
        return \Yii::$app->language;
    }

    private function getCacheKey(int $paySchetId): string
    {
        return self::CACHE_KEY . $paySchetId;
    }
}
