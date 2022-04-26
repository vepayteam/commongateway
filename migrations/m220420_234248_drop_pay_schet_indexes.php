<?php

use app\components\migration\SafeIndex;
use yii\db\Migration;

/**
 * Class m220420_234248_drop_pay_schet_indexes
 */
class m220420_234248_drop_pay_schet_indexes extends Migration
{
    use SafeIndex;

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function up()
    {
        $this->dropIndexIfExists('sms_accept_idx', 'pay_schet');
        $this->dropIndexIfExists('idx_pay_schet_sms_accept', 'pay_schet');
        $this->dropIndexIfExists('ExtBillNumber', 'pay_schet');
        $this->dropIndexIfExists('idx_pay_schet_idgroupoplat', 'pay_schet');
        $this->dropIndexIfExists('pay_schet_status_datecreate_index', 'pay_schet');

        $this->dropIndexIfExists('idx_by_rsbcron', 'pay_schet');
        $this->createIndex('idx_by_rsbcron', 'pay_schet', [
            'Status',
            'DateLastUpdate',
            'ExtBillNumber',
        ]);
    }

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function down()
    {
        $this->dropIndexIfExists('idx_by_rsbcron', 'pay_schet');
        $this->createIndex('idx_by_rsbcron', 'pay_schet', [
            'Status',
            'DateLastUpdate',
            'ExtBillNumber',
            'sms_accept',
        ]);

        $this->createIndexIfNotExists('pay_schet_status_datecreate_index', 'pay_schet', ['Status', 'DateCreate']);
        $this->createIndexIfNotExists('idx_pay_schet_idgroupoplat', 'pay_schet', 'IdGroupOplat');
        $this->createIndexIfNotExists('ExtBillNumber', 'pay_schet', 'ExtBillNumber');
        $this->createIndexIfNotExists('idx_pay_schet_sms_accept', 'pay_schet', 'sms_accept');
        $this->createIndexIfNotExists('sms_accept_idx', 'pay_schet', 'sms_accept');
    }
}
