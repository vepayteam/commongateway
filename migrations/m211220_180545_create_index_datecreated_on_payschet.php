<?php

use yii\db\Migration;

/**
 * Class m211220_180545_create_index_datecreated_on_payschet
 */
class m211220_180545_create_index_datecreated_on_payschet extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try {
            $this->createIndex('DateCreate_idx', 'pay_schet', 'DateCreate');
        } catch (Exception $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        $this->dropIndex('DateCreate_idx', 'pay_schet');
    }
}