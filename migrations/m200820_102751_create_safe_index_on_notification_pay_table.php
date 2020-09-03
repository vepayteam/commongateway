<?php

use yii\db\Migration;

/**
 * Handles the creation of indexes.
 */
class m200820_102751_create_safe_index_on_notification_pay_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            "idx_notification_pay_idpay",
            "notification_pay",
            "IdPay"
        );
        $this->createIndex(
            "idx_notification_pay_datesend",
            "notification_pay",
            "DateSend"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            "idx_notification_pay_idpay",
            "notification_pay"
        );
        $this->dropIndex(
            "idx_notification_pay_datesend",
            "notification_pay"
        );
    }
}
