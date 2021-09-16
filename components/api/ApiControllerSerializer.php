<?php

namespace app\components\api;

use yii\rest\Serializer;
use yii\web\Response;

class ApiControllerSerializer extends Serializer
{
    /**
     * Упрощенный вывод ошибок.
     */
    protected function serializeModelErrors($model)
    {
        $this->response->setStatusCode(422, 'Data Validation Failed.');

        if ($model instanceof ApiObject) {
            // Возвращает структуры json из объектов, чтобы индексы элементов в них
            // всегда указывались явно
            $this->response->formatters[Response::FORMAT_JSON]['encodeOptions'] = JSON_FORCE_OBJECT;
        }

        return $model->getFirstErrors();
    }
}