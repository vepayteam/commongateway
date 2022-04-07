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

        if (Yii::$app->db->getTableSchema($tableName, true)) {
            $this->safeDown();
        }

        $this->createTable($tableName, [
            'Id' => $this->integer(),
            'Name' => $this->string()->notNull(),
            'DefaultBankId' => $this->integer()->defaultValue(-1),
        ]);
        $this->createIndex($indexName, $tableName, 'id', $unique = true);

        foreach (UslugatovarType::typeList() as $id => $name) {
            $uslugatovarType = new UslugatovarType();
            $uslugatovarType->Id = $id;
            $uslugatovarType->Name = $name;
            $uslugatovarType->save();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableName = UslugatovarType::tableName();
        $indexName = 'idx_'.$tableName.'_id';

        try {
            $this->dropIndex($indexName, $tableName);
        } catch (\Exception $e) {}
        $this->dropTable($tableName);

        return true;
    }
}
