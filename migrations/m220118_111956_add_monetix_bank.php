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
        $bank->ID = MonetixAdapter::$bank;
        $bank->Name = 'Monetix';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Bank::deleteAll(['ID' => MonetixAdapter::$bank]);
        return true;
    }
}
