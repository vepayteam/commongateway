<?php

use yii\db\Migration;

/**
 * Class m210401_082516_add_forta_tech_bank
 */
class m210401_082516_add_forta_tech_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new \app\services\payment\models\Bank();
        $bank->ID = \app\services\payment\banks\FortaTechAdapter::$bank;
        $bank->Name = 'Forta Tech';
        $bank->save(false);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \app\services\payment\models\Bank::deleteAll(['ID' => \app\services\payment\banks\FortaTechAdapter::$bank]);
        return true;
    }
}
