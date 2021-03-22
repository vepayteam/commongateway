<?php


namespace app\services\logs\targets;


use yii\log\Target;

class SecurityTargetMixinFmt extends SecurityTargetMixin
{
    public function formatMsg($format, $args): string
    {
        return parent::formatMsg('[%s][-][-][error][%s] %s' . "\n", array_slice($args, 1));
    }
}

class SecurityStdErrTarget extends Target
{
    private $cls;
    private $stream;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->cls = new SecurityTargetMixinFmt;
        $this->stream = fopen("php://stderr", "w");
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

    public function dump($log)
    {
        fwrite($this->stream, $log);
    }

    public function export()
    {
        $this->cls->export();
    }
}
