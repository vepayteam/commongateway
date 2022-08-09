<?php

namespace app\commands;

use app\services\payment\banks\BRSAdapter;
use DateTime;
use Yii;
use yii\console\Controller;

class SeedController extends Controller
{
    public function actionBrsPaySchet($recordCount)
    {
        echo "Run BRS payschet seeding. \n";

        for ($i = 0; $i < $recordCount; $i++) {
            Yii::$app->db->createCommand()->insert('pay_schet', [
                'Bank'       => BRSAdapter::bankId(),
                'DateCreate' => (new DateTime())->modify('- '.mt_rand(1, 31).' day')->getTimestamp(),
                'IDUsluga'   => 213,
                'SummPay'   => mt_rand(1000, 50000),
                'Status'     => 1,
            ])->execute();
        }
    }
}
