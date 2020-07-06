<?php

use app\models\PaySchetPart;
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
        $this->createTable(PaySchetPart::tableName(), [
            'id' => $this->primaryKey(),
            'pay_schet_id' => $this->integer()->notNull(),
            'summ' => $this->integer()->notNull(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(PaySchetPart::tableName());
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
