<?php

use yii\db\Migration;

/**
 * Handles the creation of indexes.
 */
class m200820_105405_create_safe_index_on_uslugatovar_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            "idx_uslugatovar_idmagazin",
            "uslugatovar",
            "IdMagazin"
        );
        $this->createIndex(
            "idx_uslugatovar_iscustom",
            "uslugatovar",
            "IsCustom"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            "idx_uslugatovar_idmagazin",
            "uslugatovar"
        );
        $this->dropIndex(
            "idx_uslugatovar_iscustom",
            "uslugatovar"
        );
    }
}
