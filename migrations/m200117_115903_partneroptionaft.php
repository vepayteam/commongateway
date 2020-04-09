<?php

use yii\db\Migration;

/**
 * Class m200117_115903_partneroptionaft
 */
class m200117_115903_partneroptionaft extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner','IsAftOnly', 'tinyint(1) unsigned NOT NULL DEFAULT \'0\'');
        $this->createIndex('IsAftOnly_idx', 'partner', 'IsAftOnly');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner','IsAftOnly');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200117_115903_partneroptionaft cannot be reverted.\n";

        return false;
    }
    */
}
