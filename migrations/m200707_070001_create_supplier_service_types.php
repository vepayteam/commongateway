<?php

use app\modules\suppliers\models\SupplierServiceType;
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
        $this->createTable(SupplierServiceType::tableName(), [
            'Id' => $this->primaryKey(),
            'Name' => $this->string(250)->notNull(),
        ]);

        foreach (SupplierServiceType::SERVICE_TYPE_IDS as $k => $name) {
            $model = new SupplierServiceType();
            $model->Id = $k;
            $model->Name = $name;
            $model->save();
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(SupplierServiceType::tableName());

        return true;
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
