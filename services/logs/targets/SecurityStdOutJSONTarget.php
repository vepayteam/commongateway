<?php

namespace app\services\logs\targets;

use app\services\logs\traits\JSONFormatterTrait;

class SecurityStdOutJSONTarget extends SecurityStdOutTarget
{
    use JSONFormatterTrait;
}
