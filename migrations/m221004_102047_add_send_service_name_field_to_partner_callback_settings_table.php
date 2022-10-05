<?php

use yii\db\Migration;

/**
 * Class m221004_102047_add_send_service_name_field_to_partner_callback_settings_table
 */
class m221004_102047_add_send_service_name_field_to_partner_callback_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner_callback_settings', 'SendServiceName', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner_callback_settings', 'SendServiceName');
    }
}
