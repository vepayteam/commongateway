<?php

use yii\db\Migration;

class m220826_020750_p2p_repayment extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        $this->insert('uslugatovar_types', [
            'Id' => 27,
            'Name' => 'Погашение p2p',
        ]);

        // create 'pay_schet_p2p_repayment' table
        $this->createTable('pay_schet_p2p_repayment', [
            'paySchetId' => $this->integer()->unsigned(),
            'recipientPanTokenId' => $this->integer()->unsigned()->notNull(),
            'presetSenderPanTokenId' => $this->integer()->unsigned(),
            'presetHash' => $this->char(36),
        ]);
        $this->createIndex(
            'pay_schet_p2p_repayment_hash_idx',
            'pay_schet_p2p_repayment',
            'presetHash'
        );
        $this->addPrimaryKey(
            'pay_schet_p2p_repayment_pk',
            'pay_schet_p2p_repayment',
            'paySchetId'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->dropTable('pay_schet_p2p_repayment');

        $this->delete('uslugatovar_types', [
            'Id' => 27,
        ]);
    }
}
