<?php

use yii\db\Migration;

/**
 * Class m210215_091740_add_pay_schet_index_by_rsbcron
 */
class m210215_091740_add_pay_schet_index_by_rsbcron extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx_by_rsbcron',
            \app\services\payment\models\PaySchet::tableName(),
            [
                'Status',
                'DateLastUpdate',
                'ExtBillNumber',
                'sms_accept'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx_by_rsbcron',
            \app\services\payment\models\PaySchet::tableName()
        );

        return true;
    }
}
