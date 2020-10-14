<?php

use app\models\bank\Banks;
use yii\db\Migration;

/**
 * Class m201021_094720_add_adgroup_bank
 */
class m201021_094720_add_adgroup_bank extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $bank = new Banks();
        $bank->ID = 5;
        $bank->Name = 'AD Group';
        $bank->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Banks::deleteAll(['ID' => 5]);
        return true;
    }

}
