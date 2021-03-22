<?php


namespace app\services\logs\targets;


use yii\log\FileTarget;

class SecurityFileTarget extends FileTarget
{
    private $cls;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->cls = new SecurityTargetMixin;
    }

    public function __call($name, $params)
    {
        if (method_exists($this->cls, $name)) {
            return $this->cls->$name($params);
        }
        return parent::__call($name, $params);
    }
}
