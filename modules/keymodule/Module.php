<?php

namespace app\modules\keymodule;

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
}
