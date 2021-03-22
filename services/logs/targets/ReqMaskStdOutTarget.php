<?php

namespace app\services\logs\targets;

use yii\log\Target;

class ReqMaskStdOutTarget extends Target
{
    private $cls;
    private $stream;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->cls = new ReqMaskTargetMixin;
        $this->stream = fopen("php://stdout", "w");
    }

    function __destruct()
    {
        fclose($this->stream);
        $this->stream = null;
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
        fwrite($this->stream, implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n");
    }

    protected function getTime($timestamp)
    {
        return '';
    }
}
