<?php


namespace app\models\planner;

use app\models\payonline\Partner;
use app\models\queue\SendMailJob;
use Yii;
use yii\db\Query;

class SendNews
{
    public function execute()
    {
        $partners = Partner::findAll(['IsDeleted' => 0]);
        $result = (new Query())
            ->select('*')
            ->from('news')
            ->where(['DateSend' => 0])
            ->all();

        foreach ($result as $row) {
            Yii::$app->queue->push(new SendMailJob([
                'email' => '',
                'subject' => $row['Head'],
                'content' => $row['Body']
            ]));
        }
    }
}