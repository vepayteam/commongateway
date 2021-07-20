<?php

namespace app\services\logs\targets;

class SecurityStdOutTarget extends SecurityStreamTarget
{
    public function _open() { return fopen("php://stdout", "w"); }
}
