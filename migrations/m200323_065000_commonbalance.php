<?php

use yii\db\Migration;

/**
 * Class m200323_065000_commonbalance
 */
class m200323_065000_commonbalance extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner','IsCommonSchetVydacha', $this
            ->tinyInteger(1)->unsigned()->defaultValue(0)->notNull()
                ->comment('1 - odin schet v tcb na vydacy 0 - raznye scheta')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner','IsCommonSchetVydacha');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200323_065000_commonbalance cannot be reverted.\n";

        return false;
    }
    */
}
