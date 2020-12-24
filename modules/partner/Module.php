<?php

namespace app\modules\partner;

use Yii;

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
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $logData = [
            'action' => $action->uniqueId,
            'method' => Yii::$app->request->method,
            'isAjax' => Yii::$app->request->isAjax,
            'userId' => Yii::$app->user->isGuest ? null : Yii::$app->user->id,
        ];
        Yii::warning(json_encode($logData), 'lk');
        return parent::beforeAction($action);
    }
}
