<?php

use yii\db\Migration;

/**
 * Class m220412_124429_remove_columns_from_table_partner
 */
class m220412_124429_remove_columns_from_table_partner extends Migration
{
    private $columns = [
"MtsLogin",
"MtsPassword",
"MtsToken",
"MtsLoginAft",
"MtsPasswordAft",
"MtsTokenAft",
"MtsLoginJkh",
"MtsPasswordJkh",
"MtsTokenJkh",
"MtsLoginOct",
"MtsPasswordOct",
"MtsTokenOct",
"MtsLoginEcom",
"MtsPasswordEcom",
"MtsTokenEcom",
"MtsLoginVyvod",
"MtsPasswordVyvod",
"MtsTokenVyvod",
"MtsLoginAuto",
"MtsPasswordAuto",
"MtsTokenAuto",
"MtsLoginPerevod",
"MtsPasswordPerevod",
"MtsTokenPerevod",
"MtsLoginOctVyvod",
"MtsPasswordOctVyvod",
"MtsTokenOctVyvod",
"MtsLoginOctPerevod",
"MtsPasswordOctPerevod",
"MtsTokenOctPerevod",
"MtsPasswordParts",
"MtsTokenParts",
"MtsLoginParts",
];
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach ($this->columns as $columnName) {
            $column = $this->getDb()->getTableSchema('partner')->getColumn($columnName);
            if ($column) {
                $this->dropColumn('partner', $columnName);
            }
        }
        // Remove old migrations
        Yii::$app->db->createCommand("DELETE
    FROM migration
    WHERE version = 'm200514_132711_mtsconfig'
       OR version = 'm201001_052755_add_mts_gates'
")->execute();
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
