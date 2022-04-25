<?php

namespace app\services\payment\forms\monetix;

use app\models\traits\ValidateFormTrait;
use yii\base\Model;

abstract class BaseRequest extends Model implements \JsonSerializable
{
    use ValidateFormTrait;

    protected static $fields = [];

}