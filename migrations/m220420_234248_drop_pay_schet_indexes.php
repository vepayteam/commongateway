<?php

use yii\db\Migration;

/**
 * Class m220420_234248_drop_pay_schet_indexes
 */
class m220420_234248_drop_pay_schet_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->dropIndex('sms_accept_idx', 'pay_schet');
        $this->dropIndex('ExtBillNumber', 'pay_schet');
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->createIndex('ExtBillNumber', 'pay_schet', 'ExtBillNumber');
        $this->createIndex('sms_accept_idx', 'pay_schet', 'sms_accept');
    }
}
