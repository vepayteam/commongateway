<?php

namespace app\commands;

use app\services\YandexPayService;
use yii\console\Controller;

class YandexController extends Controller
{
    /**
     * Комманда обновляет root ключи
     *
     * @return void
     */
    public function actionRootKeysUpdate()
    {
        try {
            /** @var YandexPayService $yandexPayService */
            $yandexPayService = \Yii::$app->get(YandexPayService::class);
            $yandexPayService->updateKeys();
        } catch (\Exception $e) {
            \Yii::error(['Command yandex/root-keys-update update keys fail', $e]);
        }
    }
}