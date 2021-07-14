<?php

namespace app\services\logs\traits;

use app\helpers\Modifiers;
use Exception;

trait SecurityTargetTrait
{
    public function export()
    {
        foreach ($this->messages as $message) {
            /** @var Exception|string $exception */
            $exception = $message[0];

            if ($exception instanceof Exception) {
                $log = $exception->__toString();
            } else {
                $log = (string)$exception;
            }

            $this->dump($this->formatMessage(array_merge(
                [Modifiers::searchAndReplaceSecurity($log)],
                array_slice($message, 1)
            )));
        }
    }
}
