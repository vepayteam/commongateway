<?php

use yii\db\Migration;

/**
 * Class m211214_070029_add_send_cardmask_to_partner_callback_settings
 */
class m211214_070029_add_send_cardmask_to_partner_callback_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner_callback_settings', 'SendCardMask', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner_callback_settings', 'SendCardMask');
    }
}
