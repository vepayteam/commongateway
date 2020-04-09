<?php

namespace app\modules\partner;

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
}
