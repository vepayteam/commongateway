<?php

use yii\db\Migration;

/**
 * Class m200331_102619_addcancelurl
 */
class m200331_102619_addcancelurl extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_schet', 'CancelUrl', $this->string('1000')->after('FailedUrl'));
        $this->addColumn('uslugatovar', 'UrlReturnCancel', $this->string('1000')->after('UrlReturnFail'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_schet', 'CancelUrl');
        $this->dropColumn('uslugatovar', 'UrlReturnCancel');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200331_102619_addcancelurl cannot be reverted.\n";

        return false;
    }
    */
}
