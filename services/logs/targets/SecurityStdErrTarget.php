<?php


namespace app\services\logs\targets;


use Carbon\Carbon;
use Yii;
use yii\log\Target;

class SecurityStdErrTarget extends Target
{
    public function export()
    {
        $dbParams = require(Yii::getAlias('@app/config/db.php'));
        $stream = fopen("php://stderr", "w");

        foreach ($this->messages as $message) {
            /** @var \Exception|string $exception */
            $exception = $message[0];

            $log = '';
            if($exception instanceof \Exception) {
                $log = $exception->__toString();
            } else {
                $log = (string)$exception;
            }

            $log = sprintf('%s [%s][-][-][error][%s] %s'."\n",
                Carbon::now(),
                Yii::$app->request->remoteIP,
                $message[2],
                $this->maskByDbAccess($log, $dbParams)
            );
            fwrite($stream, $log);
        }

        fclose($stream);
    }

    private function maskByDbAccess($str, $dbParams)
    {
        $str = str_replace($dbParams['username'], '***', $str);
        $str = str_replace($dbParams['password'], '***', $str);
        return $str;
    }
}
