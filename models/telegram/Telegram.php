<?php


namespace app\models\telegram;

use Yii;
use yii\helpers\Json;

class Telegram
{
    public function GetMesages()
    {
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin - канал, из которого дочерний процесс будет читать
            1 => ["pipe", "w"],  // stdout - канал, в который дочерний процесс будет записывать
            2 => ["pipe", "a"] // stderr - файл для записи
        ];
        chdir(__DIR__);
        $cwd = '/usr/bin/python3 '. __DIR__.'/telegram.py';
        $process = proc_open($cwd, $descriptorspec, $pipes);
        if (is_resource($process)) {
            fwrite($pipes[0], "\r\n");
            fclose($pipes[0]);
            $mesgs = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            proc_close($process);
        }
        if (!empty($mesgs)) {
            $pt = fopen(Yii::$app->runtimePath . '/feed.json', 'wb');
            fwrite($pt, $mesgs);
            fclose($pt);
        }
    }

    public function ReadMesages()
    {
        $pt = fopen(Yii::$app->runtimePath . '/feed.json', 'rb');
        $mesgs = fread($pt,1000000);
        fclose($pt);
        try {
            $mesgs = iconv('windows-1251', 'utf-8', $mesgs);
            return Json::decode($mesgs);
        } catch (\Throwable $e) {
            Yii::warning($e->getMessage(), 'rsbcron');
        }
        return null;
    }
}