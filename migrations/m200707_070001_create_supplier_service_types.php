<?php

use yii\db\Migration;

/**
 * Class m200707_071901_create_supplier_service_types
 */
class m200707_070001_create_supplier_service_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('supplier_service_types', [
            'Id' => $this->primaryKey(),
            'Name' => $this->string(250)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('supplier_service_types');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200707_071901_create_supplier_service_types cannot be reverted.\n";

        return false;
    }
    */
}
