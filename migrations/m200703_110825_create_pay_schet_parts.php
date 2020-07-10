<?php

use app\models\PayschetPart;
use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m200703_110825_create_pay_schet_parts
 */
class m200703_110825_create_pay_schet_parts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(PayschetPart::tableName(), [
            'Id' => $this->primaryKey(),
            'PayschetId' => $this->integer()->notNull(),
            'PartnerId' => $this->integer()->notNull(),
            'Amount' => $this->integer()->notNull(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(PayschetPart::tableName());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200703_110825_create_pay_schet_parts cannot be reverted.\n";

        return false;
    }
    */
}
