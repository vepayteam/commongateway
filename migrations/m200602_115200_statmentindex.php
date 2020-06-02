<?php

use yii\db\Migration;

/**
 * Class m200602_115200_statmentindex
 */
class m200602_115200_statmentindex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('datepp_idx', 'statements_account', ['IdPartner', 'DatePP', 'Description', 'Bic', 'IsCredit']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('datepp_idx', 'statements_account');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200602_115200_statmentindex cannot be reverted.\n";

        return false;
    }
    */
}
