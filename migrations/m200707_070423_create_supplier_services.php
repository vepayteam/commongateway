<?php

use app\modules\suppliers\models\Supplier;
use app\modules\suppliers\models\SupplierService;
use app\modules\suppliers\models\SupplierServiceType;
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
        $this->createTable(SupplierService::tableName(), [
            'Id' => $this->primaryKey(),
            'SupplierId' => $this->integer()->notNull(),
            'TypeId' => $this->integer()->notNull(),
            'Title' => $this->string(250)->notNull(),
        ]);
        $this->addForeignKey(
            'fk-supplier_services-SupplierId',
            SupplierService::tableName(),
            'SupplierId',
            Supplier::tableName(),
            'Id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-supplier_services-TypeId',
            SupplierService::tableName(),
            'TypeId',
            SupplierServiceType::tableName(),
            'Id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-supplier_services-SupplierId',
            SupplierService::tableName()
        );
        $this->dropForeignKey(
            'fk-supplier_services-TypeId',
            SupplierService::tableName()
        );
        $this->dropTable(SupplierService::tableName());

        return true;
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
