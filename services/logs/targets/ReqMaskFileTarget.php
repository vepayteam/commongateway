<?php

namespace app\services\logs\targets;

use app\services\logs\traits\ReqMaskTargetTrait;
use yii\log\FileTarget;

class ReqMaskFileTarget extends FileTarget
{
    use ReqMaskTargetTrait;
}
