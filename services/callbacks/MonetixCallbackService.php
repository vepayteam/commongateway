<?php

namespace app\services\callbacks;

use app\services\callbacks\forms\MonetixCallbackForm;
use app\services\payment\models\PaySchet;
use Yii;
use yii\helpers\Json;

class MonetixCallbackService
{
    private const CACHE_PREFIX = 'MONETIX_CALLBACK_DATA_';
    private const CACHE_DURATION = 60 * 60;

    /**
     * @param MonetixCallbackForm $callbackForm
     * @return void
     */
    public function execCallback(MonetixCallbackForm $callbackForm)
    {
        $cacheKey = self::CACHE_PREFIX . $callbackForm->paySchetId;
        Yii::$app->cache->set($cacheKey, Json::encode($callbackForm->getAttributes()), self::CACHE_DURATION);
    }

    /**
     * @param PaySchet $paySchet
     * @return array|null
     */
    public function getCallbackData(PaySchet $paySchet)
    {
        $cacheKey = self::CACHE_PREFIX . $paySchet->ID;
        $dataJson = Yii::$app->cache->get($cacheKey);
        try {
            return Json::decode($dataJson, true);
        } catch (\Exception $e) {
            return null;
        }
    }
}