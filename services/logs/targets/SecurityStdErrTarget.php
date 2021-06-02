<?php

namespace app\services\logs\targets;

use app\services\logs\traits\SecurityTargetTrait;
use yii\log\Target;


class SecurityStdErrTarget extends Target
{
    use SecurityTargetTrait;
    public $stream;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->stream = fopen("php://stderr", "w");
    }

    function __destruct()
    {
        fclose($this->stream);
        $this->stream = null;
    }
    
    public function dump($log)
    {
        fwrite($this->stream, $log . "\n");
    }
}
