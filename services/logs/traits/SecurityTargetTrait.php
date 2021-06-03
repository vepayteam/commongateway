<?php

namespace app\services\logs\traits;

use Exception;
use Yii;

trait SecurityTargetTrait
{
    public function export()
    {
        $dbParams = require(Yii::getAlias('@app/config/db.php'));
        foreach ($this->messages as $message) {
            /** @var Exception|string $exception */
            $exception = $message[0];

            if ($exception instanceof Exception) {
                $log = $exception->__toString();
            } else {
                $log = (string)$exception;
            }

            $this->dump($this->formatMessage(array_merge(
                [$this->maskByDbAccess($log, $dbParams)],
                array_slice($message, 1)
            )));
        }
    }

    private function maskByDbAccess($str, $dbParams)
    {
        $str = str_replace($dbParams['username'], '***', $str);
        $str = str_replace($dbParams['password'], '***', $str);
        return $str;
    }
}
