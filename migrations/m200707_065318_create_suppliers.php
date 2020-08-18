<?php

use app\modules\suppliers\models\Supplier;
use yii\db\Migration;

/**
 * Class m200707_065318_create_suppliers_jkh
 */
class m200707_065318_create_suppliers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Supplier::tableName(), [
            'Id' => $this->primaryKey(),
            'Name' => $this->string(250)->notNull(),
            'SchetTcb' => $this->string(40),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(Supplier::tableName());

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200707_065318_create_suppliers_jkh cannot be reverted.\n";

        return false;
    }
    */
}
