<?php

use yii\db\Migration;

/**
 * Class m200212_124143_paysumbig
 */
class m200212_124143_paysumbig extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
        ALTER TABLE `pay_schet`
            CHANGE `SummPay` `SummPay` BIGINT(19) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'summa plateja v kopeikah', 
            CHANGE `ComissSumm` `ComissSumm` BIGINT(19) UNSIGNED DEFAULT 0 NOT NULL COMMENT 'summa komissii v kopeikah'"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200212_124143_paysumbig cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200212_124143_paysumbig cannot be reverted.\n";

        return false;
    }
    */
}
