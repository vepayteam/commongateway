<?php

use yii\db\Migration;

/**
 * Поля комисии в шлюзе (partner_bank_gates).
 */
class m210722_131819_gate_commission extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner_bank_gates', 'UseGateCompensation',
            $this->boolean()->notNull()->defaultValue(0)
        );

        $this->addColumn('partner_bank_gates', 'FeeCurrencyId', $this->integer()->null());
        $this->addForeignKey(
            'partner_bank_gates_fee_currency_fk',
            'partner_bank_gates', 'FeeCurrencyId', // from table / field
            'currency', 'Id', // to table / field
            'RESTRICT', 'CASCADE' // on delete / on update
        );

        $this->addColumn('partner_bank_gates', 'MinimalFeeCurrencyId', $this->integer()->null());
        $this->addForeignKey(
            'partner_bank_gates_minimal_fee_currency_fk',
            'partner_bank_gates', 'MinimalFeeCurrencyId', // from table / field
            'currency', 'Id', // to table / field
            'RESTRICT', 'CASCADE' // on delete / on update
        );

        $this->addColumn('partner_bank_gates', 'ClientCommission', $this->float()->null());
        $this->addColumn('partner_bank_gates', 'ClientFee', $this->float()->null());
        $this->addColumn('partner_bank_gates', 'ClientMinimalFee', $this->float()->null());

        $this->addColumn('partner_bank_gates', 'PartnerCommission', $this->float()->null());
        $this->addColumn('partner_bank_gates', 'PartnerFee', $this->float()->null());
        $this->addColumn('partner_bank_gates', 'PartnerMinimalFee', $this->float()->null());

        $this->addColumn('partner_bank_gates', 'BankCommission', $this->float()->null());
        $this->addColumn('partner_bank_gates', 'BankFee', $this->float()->null());
        $this->addColumn('partner_bank_gates', 'BankMinimalFee', $this->float()->null());
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner_bank_gates', 'BankMinimalFee');
        $this->dropColumn('partner_bank_gates', 'BankFee');
        $this->dropColumn('partner_bank_gates', 'BankCommission');

        $this->dropColumn('partner_bank_gates', 'PartnerMinimalFee');
        $this->dropColumn('partner_bank_gates', 'PartnerFee');
        $this->dropColumn('partner_bank_gates', 'PartnerCommission');

        $this->dropColumn('partner_bank_gates', 'ClientMinimalFee');
        $this->dropColumn('partner_bank_gates', 'ClientFee');
        $this->dropColumn('partner_bank_gates', 'ClientCommission');

        $this->dropForeignKey('partner_bank_gates_minimal_fee_currency_fk', 'partner_bank_gates');
        $this->dropColumn('partner_bank_gates', 'MinimalFeeCurrencyId');

        $this->dropForeignKey('partner_bank_gates_fee_currency_fk', 'partner_bank_gates');
        $this->dropColumn('partner_bank_gates', 'FeeCurrencyId');

        $this->dropColumn('partner_bank_gates', 'UseGateCompensation');
    }

}
