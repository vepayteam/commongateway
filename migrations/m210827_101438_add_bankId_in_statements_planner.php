<?php

use app\services\payment\banks\TKBankAdapter;
use app\services\statements\models\StatementsPlanner;
use yii\db\Migration;

/**
 * Class m210827_101438_add_bankId_in_statements_planner
 */
class m210827_101438_add_bankId_in_statements_planner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            StatementsPlanner::tableName(), 'BankId',
            $this->integer()->after('IdPartner')->defaultValue(TKBankAdapter::$bank)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(StatementsPlanner::tableName(), 'BankId');

        return true;
    }
}
