<?php

namespace app\commands;

use app\services\yandex\RootKeyStorageService;
use yii\console\Controller;

class YandexController extends Controller
{
    public function actionRootKeysUpdate()
    {
        try {
            /** @var RootKeyStorageService $rootKeyStorageService */
            $rootKeyStorageService = \Yii::$app->get(RootKeyStorageService::class);
            $rootKeyStorageService->updateKeys();
        } catch (\Exception $e) {
            \Yii::error(['Command yandex/root-keys-update update keys fail', $e]);
        }
    }
}