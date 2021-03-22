<?php


namespace app\services\logs\targets;


use yii\log\Target;

class SecurityStdErrTarget extends Target
{
    private $cls;
    private $stream;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->cls = new SecurityTargetMixin;
        $this->stream = fopen("php://stderr", "w");
    }

    function __destruct()
    {
        fclose($this->stream);
        $this->stream = null;
    }

    public function dump($log)
    {
        fwrite($this->stream, $log);
    }

    public function export()
    {
        $this->cls->export();
    }

    public function formatMessage($message)
    {
        list($text, $level, $category, $_) = $message;
        parent::formatMessage([$text, $level, $category, '']);
    }
}
