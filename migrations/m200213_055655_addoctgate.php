<?php

use yii\db\Migration;

/**
 * Class m200213_055655_addoctgate
 */
class m200213_055655_addoctgate extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'LoginTkbOct', $this->string(40)->after('KeyTkbEcom')->comment('oct gate'));
        $this->addColumn('partner', 'KeyTkbOct', $this->string(300)->after('LoginTkbOct'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'LoginTkbOct');
        $this->dropColumn('partner', 'KeyTkbOct');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200213_055655_addoctgate cannot be reverted.\n";

        return false;
    }
    */
}
