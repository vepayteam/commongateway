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
        $this->dropIndex('idx_pay_schet_sms_accept', 'pay_schet');
        $this->dropIndex('ExtBillNumber', 'pay_schet');
        $this->dropIndex('idx_pay_schet_idgroupoplat', 'pay_schet');
        $this->dropIndex('pay_schet_status_datecreate_index', 'pay_schet');


        $this->dropIndex('idx_by_rsbcron', 'pay_schet');
        $this->createIndex('idx_by_rsbcron', 'pay_schet', [
            'Status',
            'DateLastUpdate',
            'ExtBillNumber',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {

        $this->dropIndex('idx_by_rsbcron', 'pay_schet');
        $this->createIndex('idx_by_rsbcron', 'pay_schet', [
            'Status',
            'DateLastUpdate',
            'ExtBillNumber',
            'sms_accept',
        ]);

        $this->createIndex('pay_schet_status_datecreate_index', 'pay_schet', ['Status', 'DateCreate']);
        $this->createIndex('idx_pay_schet_idgroupoplat', 'pay_schet', 'IdGroupOplat');
        $this->createIndex('ExtBillNumber', 'pay_schet', 'ExtBillNumber');
        $this->createIndex('idx_pay_schet_sms_accept', 'pay_schet', 'sms_accept');
        $this->createIndex('sms_accept_idx', 'pay_schet', 'sms_accept');
    }
}
