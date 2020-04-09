<?php

use yii\db\Migration;

/**
 * Class m200304_125138_actupd
 */
class m200304_125138_actupd extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('act_mfo', 'IsPublic', $this
            ->tinyInteger(1)
            ->unsigned()
            ->notNull()
            ->defaultValue(0)
            ->comment('1 - opublicovan')
        );
        $this->addColumn('act_mfo', 'IsOplat', $this
            ->tinyInteger(1)
            ->unsigned()
            ->notNull()
            ->defaultValue(0)
            ->comment('1 - oplachen')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('act_mfo', 'IsPublic');
        $this->dropColumn('act_mfo', 'IsOplat');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200304_125138_actupd cannot be reverted.\n";

        return false;
    }
    */
}
