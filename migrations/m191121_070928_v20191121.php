<?php

use yii\db\Migration;

/**
 * Добавить поле url проверки возможности оплаты
 * Class m191121_070928_v20191121
 */
class m191121_070928_v20191121 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE `uslugatovar` ADD COLUMN `UrlCheckReq` VARCHAR(500) NULL COMMENT 'url dlia proverki vozmojnosti oplaty' AFTER `GroupReestrMain`");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('uslugatovar', 'UrlCheckReq');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191121_070928_v20191121 cannot be reverted.\n";

        return false;
    }
    */
}
