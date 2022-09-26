<?php

use app\services\payment\models\Bank;
use yii\db\Migration;

/**
 * Class m210528_070230_add_runa_bank
 */
class m210528_070230_add_runa_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new Bank();
        $bank->ID = 11;
        $bank->Name = 'Runa';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Bank::deleteAll(['ID' => 11]);
        return true;
    }

}
