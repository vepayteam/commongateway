<?php

use yii\db\Migration;

/**
 * Class m211214_075129_add_send_error_code_to_partner_callback_settings
 */
class m211214_075129_add_send_error_code_to_partner_callback_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner_callback_settings', 'SendErrorCode', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner_callback_settings', 'SendErrorCode');
    }
}
