<?php

use app\services\payment\banks\DectaAdapter;
use app\services\payment\models\Bank;
use yii\db\Migration;

/**
 * Class m210616_041307_add_decta_to_banks_table
 */
class m210616_041307_add_decta_to_banks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new Bank();
        $bank->ID = DectaAdapter::$bank;
        $bank->Name = 'Decta';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Bank::deleteAll(['ID' => DectaAdapter::$bank]);
        return true;
    }
}
