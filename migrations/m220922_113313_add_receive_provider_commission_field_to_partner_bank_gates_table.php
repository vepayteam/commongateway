<?php

use yii\db\Migration;

/**
 * Class m220922_113313_add_receive_provider_commission_field_to_partner_bank_gates_table
 */
class m220922_113313_add_receive_provider_commission_field_to_partner_bank_gates_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'partner_bank_gates',
            'ReceiveProviderCommission',
            $this->boolean()->defaultValue(false)->after('CurrencyId')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner_bank_gates', 'ReceiveProviderCommission');

        return true;
    }
}
