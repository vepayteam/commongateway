<?php

use yii\db\Migration;

/**
 * Handles the creation of indexes.
 */
class m200820_101951_create_safe_index_on_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            "idx_user_login",
            "user",
            "Login"
        );
        $this->createIndex(
            "idx_user_extorg",
            "user",
            "ExtOrg"
        );
        $this->createIndex(
            "idx_user_extcustomeridp",
            "user",
            "ExtCustomerIDP"
        );
        $this->createIndex(
            "idx_user_email",
            "user",
            "Email"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            "idx_user_login",
            "user"
        );
        $this->dropIndex(
            "idx_user_extorg",
            "user"
        );
        $this->dropIndex(
            "idx_user_extcustomeridp",
            "user"
        );
        $this->dropIndex(
            "idx_user_email",
            "user"
        );
    }
}
