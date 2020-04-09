<?php

use yii\db\Migration;

/**
 * Class m200220_120621_indextokens
 */
class m200220_120621_indextokens extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('findkard_indx', 'pan_token', ['FirstSixDigits', 'LastFourDigits', 'ExpDateMonth', 'ExpDateYear']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200220_120621_indextokens cannot be reverted.\n";

        return false;
    }
    */
}
