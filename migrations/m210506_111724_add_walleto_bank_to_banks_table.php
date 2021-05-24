<?php

use app\services\payment\banks\WalletoBankAdapter;
use app\services\payment\models\Bank;
use yii\db\Migration;

/**
 * Class m210506_111724_add_walleto_bank_to_banks_table
 */
class m210506_111724_add_walleto_bank_to_banks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new Bank();
        $bank->ID = WalletoBankAdapter::$bank;
        $bank->Name = 'Walleto';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Bank::deleteAll(['ID' => WalletoBankAdapter::$bank]);
        return true;
    }
}
