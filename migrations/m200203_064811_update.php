<?php

use yii\db\Migration;

/**
 * Class m200203_064811_update
 */
class m200203_064811_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('antifraud_cards','id_hash', 'user_hash');
        $this->renameColumn('antifraud_ip','id_hash', 'user_hash');
        $this->renameTable('antifraud_trans_history', 'antifraud_country');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('antifraud_country', 'antifraud_trans_history');
        $this->renameColumn('antifraud_ip', 'user_hash', 'id_hash');
        $this->renameColumn('antifraud_card', 'user_hash', 'id_hash');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200203_064811_update cannot be reverted.\n";

        return false;
    }
    */
}
