<?php

namespace app\services\logs\targets;

use app\services\logs\traits\JSONFormatterTrait;

class SecurityStdErrJSONTarget extends SecurityStdErrTarget
{
    use JSONFormatterTrait;
}
