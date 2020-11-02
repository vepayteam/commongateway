<?php

use app\services\payment\models\UslugatovarType;
use yii\db\Migration;

/**
 * Class m201012_062012_create_uslugatovar_types
 */
class m201012_062012_create_uslugatovar_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableName = UslugatovarType::tableName();
        $indexName = 'idx_'.$tableName.'_id';

        if (Yii::$app->db->getTableSchema($tableName, true) === null) {
            $this->createTable($tableName, [
                'Id' => $this->integer(),
                'Name' => $this->string()->notNull(),
                'DefaultBankId' => $this->integer()->defaultValue(-1),
            ]);
       }
       else { $this->alterColumn($tableName, 'Name', $this->string()->notNull()); }
       if(($this->db->getTable($tableName)->hasIndex($indexName)){ $this->dropIndex($indexName, $tableName); }

        $this->createIndex(
            'idx_'.UslugatovarType::tableName().'_id', UslugatovarType::tableName(),
            'id',
            $unique = true
        );

        foreach (UslugatovarType::getAll() as $id => $name) {
            $uslugatovarType = new UslugatovarType();
            $uslugatovarType->Id = $id;
            $uslugatovarType->Name = $name;
            $uslugatovarType->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_'.UslugatovarType::tableName().'_id', UslugatovarType::tableName());
        $this->dropTable(UslugatovarType::tableName());

        return true;
    }
}
