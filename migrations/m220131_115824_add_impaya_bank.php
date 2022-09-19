<?php

use app\services\payment\banks\ImpayaAdapter;
use app\services\payment\models\Bank;
use yii\db\Migration;

/**
 * Class m220131_115824_add_impaya_bank
 */
class m220131_115824_add_impaya_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new Bank();
        $bank->ID = 15;
        $bank->Name = 'Impaya';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Bank::deleteAll(['ID' => 15]);
        return true;
    }
}
