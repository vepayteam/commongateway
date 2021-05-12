<?php

use app\services\payment\models\PartnerBankGate;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%partner_bank_gates}}`.
 */
class m210429_131055_add_account_type_column_to_partner_bank_gates_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            PartnerBankGate::tableName(),
            'SchetType',
            $this->integer()->notNull()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            PartnerBankGate::tableName(),
            'SchetType'
        );
    }
}
