<?php


namespace app\services\logs\targets;


use Carbon\Carbon;
use Yii;
use yii\log\FileTarget;

class SecurityFileTarget extends FileTarget
{
    public function export()
    {
        $dbParams = require(Yii::getAlias('@app/config/db.php'));
        foreach ($this->messages as $message) {
            /** @var \Exception $exception */
            $exception = $message[0];

            $log = sprintf('%s [%s][-][-][error][%s] %s'."\n",
                Carbon::now(),
                Yii::$app->request->remoteIP,
                $message[2],
                $this->maskByDbAccess($exception->__toString(), $dbParams)
            );
            file_put_contents($this->logFile, $log, FILE_APPEND | LOCK_EX);
        }
    }

    private function maskByDbAccess($str, $dbParams)
    {
        $str = str_replace($dbParams['username'], '***', $str);
        $str = str_replace($dbParams['password'], '***', $str);
        return $str;
    }

}
