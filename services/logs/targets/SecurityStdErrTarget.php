<?php

namespace app\services\logs\targets;

class SecurityStdErrTarget extends SecurityStreamTarget
{
    public function _open() { return fopen("php://stderr", "w"); }
}
