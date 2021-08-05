<?php

namespace app\services\logs\targets;

use app\helpers\Modifiers;
use app\services\logs\traits\TraceLogTrait;
use yii\log\Target;

abstract class SecurityStreamTarget extends Target
{
    use TraceLogTrait;

    public $_stream;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_stream = $this->_open();
    }

    abstract public function _open();

    function __destruct()
    {
        fclose($this->_stream);
        $this->_stream = null;
    }

    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        $text = Modifiers::searchAndReplaceSecurity($text);
        fwrite($this->_stream, $text);
    }

    /**
     * @inheritdoc
     */
    public function getMessagePrefix($message): string
    {
        return $this->formatMessagePrefix();
    }
}
