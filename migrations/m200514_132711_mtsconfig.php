<?php

use yii\db\Migration;

/**
 * Class m200514_132711_mtsconfig
 */
class m200514_132711_mtsconfig extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'MtsLogin', $this->string(100));
        $this->addColumn('partner', 'MtsPassword', $this->string(100));
        $this->addColumn('partner', 'MtsToken', $this->string(100));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner', 'MtsLogin');
        $this->dropColumn('partner', 'MtsPassword');
        $this->dropColumn('partner', 'MtsToken');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200514_132711_mtsconfig cannot be reverted.\n";

        return false;
    }
    */
}
