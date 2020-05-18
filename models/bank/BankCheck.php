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
    public function CheckWorkedIn($bank)
    {
        $result = (new Query())
            ->select(['LastWorkIn', 'LastInPay', 'LastInCheck'])
            ->from('banks')
            ->where(['ID' => $bank, 'UsePayIn' => 1])
            ->one();

        if ($result) {
            return $result['LastInPay'] == 0 ||
                ($result['LastWorkIn'] >= $result['LastInPay'] - 10 * 60) ||
                ($result['LastInCheck'] > time() - 5 * 60) ||
                ($result['LastWorkIn'] < $result['LastInPay'] - 10 * 60 && $result['LastInPay'] < time() - 20 * 60);
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
    public function UpdateLastCheck($bank)
    {
        Yii::$app->db->createCommand()->update('banks', [
            'LastInCheck' => time()
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