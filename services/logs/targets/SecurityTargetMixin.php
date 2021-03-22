<?php


namespace app\services\logs\targets;


use Carbon\Carbon;
use Yii;

class SecurityTargetMixin
{
    public function export()
    {
        $dbParams = require(Yii::getAlias('@app/config/db.php'));
        foreach ($this->messages as $message) {
            /** @var \Exception|string $exception */
            $exception = $message[0];

            if ($exception instanceof \Exception) {
                $log = $exception->__toString();
            } else {
                $log = (string)$exception;
            }

            $this->dump($this->formatMsg(
                '%s [%s][-][-][error][%s] %s' . "\n",
                [
                    Carbon::now(),
                    Yii::$app->request->remoteIP,
                    $message[2],
                    $this->maskByDbAccess($log, $dbParams)
                ]
            ));
        }
    }

    private function formatMsg($format, $args): string
    {
        return sprintf($format, ...$args);
    }

    private function maskByDbAccess($str, $dbParams)
    {
        $str = str_replace($dbParams['username'], '***', $str);
        $str = str_replace($dbParams['password'], '***', $str);
        return $str;
    }

    public function dump($log) {}
}
