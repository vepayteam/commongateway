<?php

use yii\db\Migration;

/**
 * Class m200114_075823_securelogin
 */
class m200114_075823_securelogin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner_users','DateLastLogin','int(10) unsigned DEFAULT 0');
        $this->addColumn('partner_users','ErrorLoginCnt','int(10) unsigned DEFAULT 0');
        $this->addColumn('partner_users','DateErrorLogin','int(10) unsigned DEFAULT 0');
        $this->addColumn('partner_users','AutoLockDate','int(10) unsigned DEFAULT 0');

        $this->addColumn('key_users','ErrorLoginCnt','int(10) unsigned DEFAULT 0');
        $this->addColumn('key_users','DateErrorLogin','int(10) unsigned DEFAULT 0');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner_users','DateLastLogin');
        $this->dropColumn('partner_users','ErrorLoginCnt');
        $this->dropColumn('partner_users','DateErrorLogin');
        $this->dropColumn('partner_users','AutoLockDate');

        $this->dropColumn('key_users','ErrorLoginCnt');
        $this->dropColumn('key_users','DateErrorLogin');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200114_075823_securelogin cannot be reverted.\n";

        return false;
    }
    */
}
