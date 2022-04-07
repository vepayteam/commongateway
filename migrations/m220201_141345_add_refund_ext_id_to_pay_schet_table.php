<?php

use yii\db\Migration;

/**
 * Class m220201_141345_add_refund_ext_id_to_pay_schet_table
 */
class m220201_141345_add_refund_ext_id_to_pay_schet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_schet', 'RefundExtId', $this->string(50)->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_schet', 'RefundExtId');
    }
}
