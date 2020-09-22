<?php


namespace app\services\logs\targets;


use Carbon\Carbon;
use Yii;
use yii\log\FileTarget;

class DbFileTarget extends FileTarget
{
    public function export()
    {
        $dbParams = require(Yii::getAlias('@app/config/db.php'));
        foreach ($this->messages as $message) {
            if($message[0] instanceof \yii\db\Exception) {
                /** @var \Exception $exception */
                $exception = $message[0];

                $log = sprintf('%s [%s][-][-][error][yii\db\Exception] PDOException: %s in %s:%s'."\n",
                    Carbon::now(),
                    Yii::$app->request->remoteIP,
                    $this->maskByDbAccess($exception->getMessage(), $dbParams),
                    $exception->getFile(),
                    $exception->getLine()
                );
                file_put_contents($this->logFile, $log, FILE_APPEND | LOCK_EX);
            }
        }
    }

    private function maskByDbAccess($str, $dbParams)
    {
        $str = str_replace($dbParams['username'], '***', $str);
        $str = str_replace($dbParams['password'], '***', $str);
        return $str;
    }
}
