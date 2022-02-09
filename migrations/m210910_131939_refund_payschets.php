<?php

use yii\db\Migration;

/**
 * Class m210910_131939_refund_payschets
 */
class m210910_131939_refund_payschets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->addColumn('pay_schet', 'RefundSourceId', $this->integer()->unsigned());
        $this->addForeignKey(
            'pay_schet_refund_source_id_fk',
            'pay_schet', 'RefundSourceId', // from table / field
            'pay_schet', 'ID', // to table / field
            'RESTRICT', 'CASCADE' // on delete / on update
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropForeignKey('pay_schet_refund_source_id_fk', 'pay_schet');
        $this->dropColumn('pay_schet', 'RefundSourceId');
    }
}
