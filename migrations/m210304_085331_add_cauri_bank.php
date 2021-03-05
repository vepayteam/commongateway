<?php

use yii\db\Migration;

/**
 * Class m210304_085331_add_cauri_bank
 */
class m210304_085331_add_cauri_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new \app\services\payment\models\Bank();
        $bank->ID = \app\services\payment\banks\CauriAdapter::$bank;
        $bank->Name = 'Cauri';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \app\services\payment\models\Bank::deleteAll(['ID' => \app\services\payment\banks\CauriAdapter::$bank]);
        return true;
    }
}
