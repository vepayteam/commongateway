<?php

use yii\db\Migration;

/**
 * Class m200525_072522_notifind
 */
class m200525_072522_notifind extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('TypeNotif_idx','notification_pay');
        $this->createIndex('TypeNotif_idx','notification_pay', ['DateCreate', 'TypeNotif', 'IdPay']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('TypeNotif_idx', 'notification_pay');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200525_072522_notifind cannot be reverted.\n";

        return false;
    }
    */
}
