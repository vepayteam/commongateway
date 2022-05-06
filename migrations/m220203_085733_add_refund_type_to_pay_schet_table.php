<?php

use yii\db\Migration;

/**
 * Class m220203_085733_add_refund_type_to_pay_schet_table
 */
class m220203_085733_add_refund_type_to_pay_schet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'pay_schet',
            'RefundType',
            $this->tinyInteger()
                ->unsigned()
                ->null()
                ->defaultValue(null)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_schet', 'RefundType');
    }
}
