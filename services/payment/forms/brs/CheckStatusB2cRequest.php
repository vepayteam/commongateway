<?php

namespace app\services\payment\forms\brs;

use yii\base\Model;

class CheckStatusB2cRequest extends Model
{
    public $sourceId;
    public $operationId;
}
