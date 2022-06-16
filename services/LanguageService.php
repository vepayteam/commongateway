<?php

namespace app\services;

use app\services\payment\models\PaySchetLanguage;
use yii\base\Component;

class LanguageService extends Component
{
    const API_LANG_RUS = 'rus';
    const API_LANG_ENG = 'eng';

    const APP_LANG_RUS = 'ru-RU';
    const APP_LANG_ENG = 'en-US';

    const ALL_API_LANG_LIST = [self::API_LANG_RUS, self::API_LANG_ENG];

    private $apiLangToAppLang = [
        self::API_LANG_RUS => self::APP_LANG_RUS,
        self::API_LANG_ENG => self::APP_LANG_ENG,
    ];

    /**
     * Сохраняет язык для конкретного paySchet
     *
     * @param int $paySchetId
     * @param string|null $apiLanguage
     * @return void
     */
    public function saveApiLanguage(int $paySchetId, ?string $apiLanguage)
    {
        /**
         * Если apiLanguage не передан, то просто выходим из функции и ничего не сохраняем в таблицу,
         * в дальнейшем значение языка приложения будет по умолчанию ru-RU
         */
        if (!$apiLanguage) {
            return;
        }

        $paySchetLanguage = PaySchetLanguage::findOne([
            'paySchetId' => $paySchetId,
        ]);

        if (!$paySchetLanguage) {
            $paySchetLanguage = new PaySchetLanguage();
            $paySchetLanguage->paySchetId = $paySchetId;
        }

        $paySchetLanguage->apiLanguage = $apiLanguage;
        $paySchetLanguage->save();
    }

    /**
     * Устанавливает язык приложения для конкретного paySchet
     *
     * @param int $paySchetId
     * @return void
     */
    public function setAppLanguage(int $paySchetId)
    {
        $paySchetLanguage = PaySchetLanguage::findOne([
            'paySchetId' => $paySchetId,
        ]);

        if ($paySchetLanguage) {
            \Yii::$app->language = $this->apiLangToAppLang[$paySchetLanguage->apiLanguage];
        } else {
            \Yii::$app->language = self::APP_LANG_RUS;
        }
    }

    /**
     * Возвращает язык приложения
     *
     * @return string
     */
    public function getAppLanguage(): string
    {
        return \Yii::$app->language;
    }
}
