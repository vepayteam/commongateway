<?php

use yii\db\Migration;

/**
 * Class m201009_074919_create_partner_bank_gates
 */
class m201009_074919_create_partner_bank_gates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('partner_bank_gates', [
            'Id' => $this->primaryKey(),
            'PartnerId' => $this->integer()->notNull(),
            'BankId' => $this->integer()->notNull(),
            'TU' => $this->string(),
            'Login' => $this->string(),
            'Token' => $this->string(),
            'Password' => $this->string(),
            'AdvParams_1' => $this->string(),
            'AdvParams_2' => $this->string(),
            'AdvParams_3' => $this->string(),
            'AdvParams_4' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('partner_bank_gates');

        return true;
    }
}
