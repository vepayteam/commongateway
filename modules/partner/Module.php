<?php

namespace app\modules\partner;

use app\modules\partner\services\AdminSettingsService;
use app\modules\partner\services\IdentificationStatisticService;
use app\modules\partner\services\PartService;
use Yii;
use yii\base\InvalidConfigException;

/**
 * partner module definition class
 */
class Module extends \yii\base\Module
{
    public $layout = 'partner';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\partner\controllers';

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!\Yii::$app->has(AdminSettingsService::class)) {
            \Yii::$app->set(AdminSettingsService::class, AdminSettingsService::class);
        }
        if (!\Yii::$app->has(PartService::class)) {
            \Yii::$app->set(PartService::class, PartService::class);
        }
        if (!\Yii::$app->has(IdentificationStatisticService::class)) {
            \Yii::$app->set(IdentificationStatisticService::class, IdentificationStatisticService::class);
        }
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $logData = [
            'action' => $action->uniqueId,
            'ip' => Yii::$app->request->remoteIP,
            'method' => Yii::$app->request->method,
            'isAjax' => Yii::$app->request->isAjax,
            'userId' => Yii::$app->user->isGuest ? null : Yii::$app->user->id,
        ];
        Yii::warning(json_encode($logData), 'lk');
        return parent::beforeAction($action);
    }
}
