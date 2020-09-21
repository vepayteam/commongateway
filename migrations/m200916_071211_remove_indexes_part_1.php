<?php

use yii\db\Migration;

/**
 * Class m200916_071211_remove_indexes_part_1
 */
class m200916_071211_remove_indexes_part_1 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex(
            "idx_pay_schet_datecreate",
            "pay_schet"
        );
        $this->dropIndex(
            "idx_pay_schet_sms_accept",
            "pay_schet"
        );
        $this->dropIndex(
            "idx_pay_schet_idorg",
            "pay_schet"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createIndex(
            "idx_pay_schet_datecreate",
            "pay_schet",
            "DateCreate"
        );
        $this->createIndex(
            "idx_pay_schet_sms_accept",
            "pay_schet",
            "sms_accept"
        );
        $this->createIndex(
            "idx_pay_schet_idorg",
            "pay_schet",
            "IdOrg"
        );
    }
}
