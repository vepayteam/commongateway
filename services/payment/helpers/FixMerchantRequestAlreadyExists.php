<?php


namespace app\services\payment\helpers;

use app\services\payment\models\PaySchet;
use Yii;
use yii\web\Response;

class FixMerchantRequestAlreadyExists
{
    /**
     * @throws \yii\db\Exception
     */
    public function fix()
    {
        $sql = "SELECT
                    ps.ID,
                    ps.ErrorInfo
                FROM pay_schet ps 
                WHERE ps.ErrorInfo LIKE :like
                AND ps.DateCreate > :date";
        $ps = Yii::$app->db->createCommand($sql,[':like' => 'Заявка c ExtId%', ':date' => 1606770000])->queryAll();
        foreach ($ps as $key => $value) {
            $paySchet = PaySchet::findOne([
                'ID' => (int)$value['ID'],
            ]);
            if(!$paySchet) {
                return;
            }
            $paySchet->Status = 0;
            $paySchet->ErrorInfo = 'Запрашивается статус';
            $paySchet->sms_accept = 1;
            if($paySchet->save(false)) {
                echo 'Статус сброшен, ожидается обновление';
            } else {
                echo 'Ошибка обновления';
            }
        }
    }
}
