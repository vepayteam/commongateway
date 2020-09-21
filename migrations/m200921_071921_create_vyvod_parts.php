<?php

use app\models\partner\admin\VyvodParts;
use yii\db\Migration;

/**
 * Class m200921_071921_create_vyvod_parts
 */
class m200921_071921_create_vyvod_parts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(VyvodParts::tableName(), [
            'Id' => $this->primaryKey(),
            'SenderId' => $this->integer()->notNull(),
            'RecipientId' => $this->integer()->notNull(),
            'PaychetId' => $this->integer()->notNull(),
            'Amount' => $this->integer()->notNull(),
            'DateCreate' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(VyvodParts::tableName());

        return true;
    }

}
