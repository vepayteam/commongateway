<?php

use yii\db\Migration;

/**
 * Class m220512_131006_remove_fields_from_table_uslugatovar
 */
class m220512_131006_remove_fields_from_table_uslugatovar extends Migration
{
    private $columns = [
        'SchetchikNames',
        'PatternFind',
        'QrcodeExportFormat',
        'SendToGisjkh',
    ];
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->columns as $columnName) {
            $column = $this->getDb()->getTableSchema('uslugatovar')->getColumn($columnName);
            if ($column) {
                $this->dropColumn('uslugatovar', $columnName);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220512_131006_remove_fields_from_table_uslugatovar cannot be reverted.\n";

        return true;
    }
}