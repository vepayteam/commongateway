<?php

namespace app\services\payment\models\repositories;

use app\services\payment\models\Bank;

class BankRepository
{
    /**
     * @param int $id
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getBankById(int $id)
    {
        return Bank::find()
            ->where([
                'ID' => $id
            ])
            ->one();
    }
}
