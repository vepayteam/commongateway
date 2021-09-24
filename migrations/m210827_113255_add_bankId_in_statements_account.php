<?php

use app\services\payment\banks\TKBankAdapter;
use app\services\statements\models\StatementsAccount;
use yii\db\Migration;

/**
 * Class m210827_113255_add_bankId_in_statements_account
 */
class m210827_113255_add_bankId_in_statements_account extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            StatementsAccount::tableName(), 'BankId',
            $this->integer()->after('IdPartner')->defaultValue(TKBankAdapter::$bank)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(StatementsAccount::tableName(), 'BankId');

        return true;
    }
}
