<?php

use yii\db\Migration;

/**
 * Class m220415_131021_refund_index_payschets
 */
class m220415_131021_refund_index_payschets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE pay_schet
                DROP CONSTRAINT IF EXISTS pay_schet_refund_source_id_fk
        ");

        $this->createIndex('refund_source_id_idx', 'pay_schet', 'RefundSourceId');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("
            ALTER TABLE pay_schet
                ADD CONSTRAINT pay_schet_refund_source_id_fk
                    FOREIGN KEY IF NOT EXISTS (RefundSourceId) REFERENCES pay_schet(ID)
                    ON DELETE RESTRICT ON UPDATE CASCADE
        ");

        $this->dropIndex('refund_source_id_idx', 'pay_schet');
    }
}