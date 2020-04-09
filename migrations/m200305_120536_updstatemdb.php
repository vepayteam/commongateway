<?php

use yii\db\Migration;

/**
 * Class m200305_120536_updstatemdb
 */
class m200305_120536_updstatemdb extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('statements_account', 'Kpp', $this->string(50));
        $this->addColumn('statements_account', 'DateRead', $this
            ->integer(10)
            ->unsigned()
            ->notNull()
            ->defaultValue(0)
            ->comment('data poluchenia ot tkb')
        );

        $this->createIndex('DateRead_idx','statements_account', ['DateRead', 'IdPartner', 'TypeAccount']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('DateRead_idx','statements_account');
        $this->dropColumn('statements_account', 'DateRead');
        $this->dropColumn('statements_account', 'Kpp');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200305_120536_updstatemdb cannot be reverted.\n";

        return false;
    }
    */
}
