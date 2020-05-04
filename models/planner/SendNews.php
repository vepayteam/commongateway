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
        try {
            $partners = Partner::findAll(['IsDeleted' => 0]);

            $result = (new Query())
                ->select('*')
                ->from('news')
                ->where(['DateSend' => 0])
                ->all();

            foreach ($result as $row) {
                foreach ($partners as $partner) {
                    if (!empty($partner->EmailNotif)) {
                        Yii::$app->queue->push(new SendMailJob([
                            'email' => $partner->EmailNotif,
                            'subject' => $row['Head'],
                            'content' => date('d.m.Y H:i', $row['DateAdd'])."<br>".str_replace("\r\n", "<br>", $row['Body'])
                        ]));
                    }
                }

                Yii::$app->db->createCommand()->update('news', ['DateSend' => time()], ['ID' => $row['ID']])->execute();
            }
        } catch (\Throwable $e) {
            Yii::warning($e->getMessage(), 'rsbcron');
        }
    }
}