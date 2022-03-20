<?php

namespace app\modules\mfo\components;

use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\web\Response;

abstract class BaseApiController extends \yii\rest\Controller
{
    /**
     * {@inheritDoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'corsFilter' => Cors::class,
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'text/html' => Response::FORMAT_JSON,
                ],
            ],
        ]);
    }
}