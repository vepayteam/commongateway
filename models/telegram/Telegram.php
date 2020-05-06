<?php


namespace app\models\telegram;

use Yii;
use yii\helpers\Json;

class Telegram
{
    public function GetMesages()
    {
        chdir(__DIR__);
        $file = Yii::$app->runtimePath . '/feed.json';
        $cwd = '/usr/bin/python3 '. __DIR__.'/telegram.py '. $file;
        shell_exec($cwd);
    }

    public function ReadMesages()
    {
        $pt = fopen(Yii::$app->runtimePath . '/feed.json', 'rb');
        $mesgs = fread($pt,1000000);
        fclose($pt);
        try {
            if (PHP_OS_FAMILY === "Windows") {
                $mesgs = iconv('windows-1251', 'utf-8', $mesgs);
            }
            return Json::decode($mesgs);
        } catch (\Throwable $e) {
            Yii::warning($e->getMessage(), 'rsbcron');
        }
        return null;
    }
}