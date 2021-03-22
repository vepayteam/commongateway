<?php

namespace app\services\logs\targets;

use yii\log\Target;

class ReqMaskStdOutTarget extends Target
{
    private $cls;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->cls = new ReqMaskTargetMixin;
    }

    public function __call($name, $params)
    {
        if (method_exists($this->cls, $name)) {
            return $this->cls->$name($params);
        }
        return parent::__call($name, $params);
    }

    public function export()
    {
        $stream = fopen("php://stdout", "w");
        fwrite($stream, implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n");
        fclose($stream);
    }
}
