<?php

use yii\db\Migration;

/**
 * Вознаграждение ТКБ по выдаче
 * Class m191120_125930_v20191120
 */
class m191120_125930_v20191120 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //$this->addColumn('banks', 'OCTVozn', $this->double());
        //$this->addColumn('banks', 'FreepayVozn', $this->double());
        $this->execute("ALTER TABLE `banks` ADD COLUMN `OCTVozn` DOUBLE DEFAULT 0 NOT NULL AFTER `OCTComisMin`, ADD COLUMN `FreepayVozn` DOUBLE DEFAULT 0 NOT NULL AFTER `FreepayComisMin`");
        $this->update('banks',[
            'OCTVozn' => 0.5,
            'FreepayVozn' => 0.5
        ], 'ID=2');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('banks', 'OCTVozn');
        $this->dropColumn('banks', 'FreepayVozn');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191120_125930_v20191120 cannot be reverted.\n";

        return false;
    }
    */
}
