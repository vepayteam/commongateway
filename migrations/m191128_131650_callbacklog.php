<?php

use yii\db\Migration;

/**
 * Class m191128_131650_callbacklog
 */
class m191128_131650_callbacklog extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('notification_pay', 'SendCount', "INT(10) unsigned DEFAULT 0 NOT NULL");
        $this->addCommentOnColumn('notification_pay', 'SendCount', 'chislo popytok');
        $this->addColumn('notification_pay','DateLastReq', "INT(10) unsigned DEFAULT 0 NOT NULL");
        $this->addCommentOnColumn('notification_pay','DateLastReq', 'data zaprosa');
        $this->createIndex('idx_DateLastReq', '{{%notification_pay}}', 'DateLastReq');
        $this->addColumn('notification_pay', 'FullReq', 'TEXT');
        $this->addCommentOnColumn('notification_pay','FullReq', 'polnuii adres zaprosa');
        $this->addColumn('notification_pay', 'HttpCode', 'INT(10) unsigned DEFAULT 0 NOT NULL');
        $this->addCommentOnColumn('notification_pay', 'HttpCode', 'kod http otveta');
        $this->addColumn('notification_pay', 'HttpAns', 'TEXT');
        $this->addCommentOnColumn('notification_pay', 'HttpAns', 'tekst http otveta');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('notification_pay', 'SendCount');
        $this->dropIndex('idx_DateLastReq', 'notification_pay');
        $this->dropColumn('notification_pay', 'DateLastReq');
        $this->dropColumn('notification_pay', 'HttpCode');
        $this->dropColumn('notification_pay', 'HttpAns');
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191128_131650_callbacklog cannot be reverted.\n";

        return false;
    }
    */
}
