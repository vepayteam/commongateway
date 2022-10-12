<?php

use yii\db\Migration;

/**
 * Class m221012_111629_remove_column_DefaultBankId_from_table_uslugatovar_types
 */
class m221012_111629_remove_column_DefaultBankId_from_table_uslugatovar_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $column = $this->getDb()->getTableSchema('uslugatovar_types')->getColumn('DefaultBankId');
        if ($column) {
            $this->dropColumn('partner', 'DefaultBankId');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220412_124429_remove_columns_from_table_partner cannot be reverted.\n";

        return true;
    }
}
