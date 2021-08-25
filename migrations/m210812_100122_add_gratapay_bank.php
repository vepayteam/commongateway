<?php

use app\services\payment\banks\GratapayAdapter;
use app\services\payment\models\Bank;
use yii\db\Migration;

/**
 * Class m210812_100122_add_gratapay_bank
 */
class m210812_100122_add_gratapay_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new Bank();
        $bank->ID = GratapayAdapter::$bank;
        $bank->Name = 'Gratapay';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Bank::deleteAll(['ID' => GratapayAdapter::$bank]);
        return true;
    }
}
