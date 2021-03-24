<?php

namespace app\services\logs\targets;

use app\services\logs\traits\ReqMaskTargetTrait;
use yii\log\Target;

class ReqMaskStdOutTarget extends Target
{
    use ReqMaskTargetTrait;

    private $stream;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->stream = fopen("php://stdout", "w");
    }

    function __destruct()
    {
        fclose($this->stream);
        $this->stream = null;
    }

    public function export()
    {
        fwrite($this->stream, implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n");
    }

    protected function getTime($timestamp): string
    {
        return '';
    }
}
