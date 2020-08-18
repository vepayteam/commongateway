<?php

use app\modules\suppliers\models\Supplier;
use app\modules\suppliers\models\SupplierService;
use app\modules\suppliers\models\SupplierServiceType;
use yii\db\Migration;

/**
 * Class m200708_082823_add_partner_id_in_suppliers
 */
class m200708_082823_add_partner_id_in_suppliers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(Supplier::tableName(), 'PartnerId', $this->integer()->notNull());
        $this->addColumn(SupplierService::tableName(), 'PartnerId', $this->integer()->notNull());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(Supplier::tableName(), 'PartnerId');
        $this->dropColumn(SupplierService::tableName(), 'PartnerId');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200708_082823_add_partner_id_in_suppliers cannot be reverted.\n";

        return false;
    }
    */
}
