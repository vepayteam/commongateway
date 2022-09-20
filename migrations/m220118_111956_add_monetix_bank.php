<?php

use app\services\payment\banks\MonetixAdapter;
use app\services\payment\models\Bank;
use yii\db\Migration;

/**
 * Class m220118_111956_add_monetix_bank
 */
class m220118_111956_add_monetix_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new Bank();
        $bank->ID = 14;
        $bank->Name = 'Monetix';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Bank::deleteAll(['ID' => 14]);
        return true;
    }
}
