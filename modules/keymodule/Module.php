<?php

namespace app\modules\keymodule;

use Yii;

/**
 * keymodule module definition class
 */
class Module extends \yii\base\Module
{
    public $layout = 'keymodule';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\keymodule\controllers';

    /**
     * {@inheritdoc}
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
            'ip' => Yii::$app->request->remoteIP,
            'method' => Yii::$app->request->method,
            'isAjax' => Yii::$app->request->isAjax,
            'userId' => isset(Yii::$app->session['KeyUser']) && Yii::$app->session['KeyUser'] ? Yii::$app->session['KeyUser'] : null,
        ];
        Yii::warning(json_encode($logData), 'keymodule');
        return parent::beforeAction($action);
    }

}
