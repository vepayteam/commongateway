<?php

use yii\db\Migration;

/**
 * Handles the creation of indexes.
 */
class m200820_101536_create_safe_index_on_antifraud_bin_banks_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            "idx_antifraud_bin_banks_country",
            "antifraud_bin_banks",
            "country"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            "idx_antifraud_bin_banks_country",
            "antifraud_bin_banks"
        );
    }
}
