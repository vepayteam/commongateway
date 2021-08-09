<?php

namespace app\modules\hhapi;

/**
 * Host to Host API.
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = __NAMESPACE__ . '\controllers';

    /**
     * {@inheritDoc}
     */
    public function init()
    {

        parent::init();

        $this->modules = [
            'v1' => [
                'class' => \app\modules\hhapi\v1\Module::class,
            ],
        ];
    }
}
