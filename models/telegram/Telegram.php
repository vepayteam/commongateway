<?php


namespace app\models\telegram;

use Yii;
use yii\helpers\Json;

class Telegram
{
    public function GetMesages()
    {
        exec('python3 telegram.py > data.json');
        $pt = fopen(__dir__.'/data.json', 'rb');
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