<?php

use yii\db\Migration;

/**
 * Class m200707_070423_create_supplier_services
 */
class m200707_070423_create_supplier_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('supplier_services', [
            'Id' => $this->primaryKey(),
            'SupplierId' => $this->integer()->notNull(),
            'TypeId' => $this->integer()->notNull(),
            'Title' => $this->string(250)->notNull(),
        ]);
        $this->addForeignKey(
            'fk-supplier_services-SupplierId',
            'supplier_services',
            'SupplierId',
            'suppliers',
            'Id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-supplier_services-TypeId',
            'supplier_services',
            'TypeId',
            'supplier_service_types',
            'Id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('supplier_services');
        $this->dropForeignKey(
            'fk-supplier_services-SupplierId',
            'supplier_services'
        );
        $this->dropForeignKey(
            'fk-supplier_services-TypeId',
            'supplier_services'
        );

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200707_070423_create_supplier_services cannot be reverted.\n";

        return false;
    }
    */
}
