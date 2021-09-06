<?php

use yii\db\Migration;

/**
 * Class m210803_112038_add_http_code_index_in_notification_pay_table
 */
class m210803_112038_add_http_code_index_in_notification_pay_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx_notification_pay_http_code', 'notification_pay', 'HttpCode');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_notification_pay_http_code', 'notification_pay');
    }
}
