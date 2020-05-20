<?php

namespace app\models\bank;

use Yii;
use yii\db\Query;

class BankCheck
{
    /**
     * @param int $bank
     * @return int
     */
    public function CheckWorkedIn($offset = 0)
    {
        $bank = (new Query())
            ->select(['ID', 'LastWorkIn', 'LastInPay', 'LastInCheck'])
            ->from('banks')
            ->where(['UsePayIn' => 1])
            ->orderBy('SortOrder')
            ->offset($offset)
            ->limit(1)
            ->one();

        $selected = 0;
        if ($bank) {
            $selected = $bank['ID'];
        }
        return $selected;
    }

    public function CheckWorkedApplePay()
    {
        $result = (new Query())
            ->select(['ID'])
            ->from('banks')
            ->where(['UsePayIn' => 1, 'UseApplePay' => 1])
            ->orderBy('SortOrder')
            ->limit(1)
            ->one();

        if ($result) {
            return $result['ID'];
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