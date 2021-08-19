<?php

namespace app\modules\h2hapi\v1;

use app\modules\h2hapi\v1\services\InvoiceApiService;
use app\modules\h2hapi\v1\services\PaymentApiService;
use yii\base\InvalidConfigException;

/**
 * Host to Host API version 1.
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = __NAMESPACE__ . '\controllers';

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!\Yii::$app->has(InvoiceApiService::class)) {
            \Yii::$app->set(InvoiceApiService::class, InvoiceApiService::class);
        }
        if (!\Yii::$app->has(PaymentApiService::class)) {
            \Yii::$app->set(PaymentApiService::class, PaymentApiService::class);
        }
    }
}
