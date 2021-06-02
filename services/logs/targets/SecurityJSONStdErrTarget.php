<?php

namespace app\services\logs\targets;

use app\services\logs\traits\JSONFormatterTrait;
use yii\helpers\Json;


class SecurityJSONStdErrTarget extends SecurityStdErrTarget
{
    use JSONFormatterTrait;

    public function dump($log)
    {
        parent::dump(Json::encode($log) . "\n");
    }
}
