<?php

use yii\db\Migration;

/**
 * Class m200525_070551_notifindex
 */
class m200525_070551_notifindex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('TypeNotif_idx', 'notification_pay', ['TypeNotif', 'DateCreate']);
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
        echo "m200525_070551_notifindex cannot be reverted.\n";

        return false;
    }
    */
}
