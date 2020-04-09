<?php

use yii\db\Migration;

/**
 * Class m200312_074530_afindex
 */
class m200312_074530_afindex extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('rule_idx', 'antifraud_stat', ['rule', 'success']);
        $this->addColumn('partner', 'IsAutoPerevodToVydacha', $this
            ->tinyInteger(1)
            ->unsigned()
            ->notNull()
            ->defaultValue(0)
            ->comment('avtoperevod na vydachy')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('rule_idx','antifraud_stat');
        $this->dropColumn('partner', 'IsAutoPerevodToVydacha');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200312_074530_afindex cannot be reverted.\n";

        return false;
    }
    */
}
