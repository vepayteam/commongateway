<?php

namespace app\services\logs\targets;

use app\services\logs\traits\JSONFormatterTrait;
use yii\helpers\Json;

class ReqMaskJSONStdOutTarget extends ReqMaskStdOutTarget
{
    use JSONFormatterTrait;

    public function export()
    {
        foreach (array_map([$this, 'formatMessage'], $this->messages) as $message) {
            fwrite($this->stream, Json::encode($message) . "\n");
        }
    }
}
