<?php

namespace app\models\bank;

use Yii;
use yii\db\Query;

class BankCheck
{
    /**
     * @param int $bank
     * @return bool
     */
    public function CheckWorked($bank)
    {
        $result = (new Query())
            ->select(['LastWorkIn', 'LastInPay'])
            ->from('banks')
            ->where(['ID' => $bank, 'UsePayIn' => true])
            ->one();

        if ($result) {
            return $result['LastWorkIn'] > time() - 10 * 60 || $result['LastWorkIn'] < $result['LastInPay'] - 60 * 60;
        }
        return false;
    }

    /**
     * @param $bank
     * @throws \yii\db\Exception
     */
    public function UpdatePay($bank)
    {
        Yii::$app->db->createCommand()->update('banks', [
            'LastInPay' => time()
        ], ['ID' => $bank])->execute();
    }

    /**
     * @param $bank
     * @throws \yii\db\Exception
     */
    public function UpdateLastWork($bank)
    {
        Yii::$app->db->createCommand()->update('banks', [
            'LastWorkIn' => time()
        ], ['ID' => $bank])->execute();
    }
}