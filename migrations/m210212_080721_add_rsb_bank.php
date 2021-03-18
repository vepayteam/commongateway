<?php

use yii\db\Migration;

/**
 * Class m210212_080721_add_rsb_bank
 */
class m210212_080721_add_rsb_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new \app\services\payment\models\Bank();
        $bank->ID = \app\services\payment\banks\BRSAdapter::$bank;
        $bank->Name = 'RSB';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \app\services\payment\models\Bank::deleteAll(['ID' => \app\services\payment\banks\BRSAdapter::$bank]);
        return true;
    }
}
