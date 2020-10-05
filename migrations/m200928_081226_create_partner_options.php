<?php

use app\services\partners\models\PartnerOption;
use yii\db\Migration;

/**
 * Class m200928_081226_create_partner_options
 */
class m200928_081226_create_partner_options extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(PartnerOption::tableName(), [
            'Id' => $this->primaryKey(),
            'PartnerId' => $this->integer()->notNull(),
            'Name' => $this->string()->notNull(),
            'Value' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(PartnerOption::tableName());

        return true;
    }

}
