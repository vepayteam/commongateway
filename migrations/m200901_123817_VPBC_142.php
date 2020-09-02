<?php

use yii\db\Migration;

/**
 * Class m200901_123817_VPBC_142
 */
class m200901_123817_VPBC_142 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = "SHOW COLUMNS FROM `order_pay` LIKE 'OrderTo'";
        if(!$this->db->createCommand($sql)->query()->count()) {
            $sql = "ALTER TABLE `order_pay` ADD COLUMN `OrderTo` TEXT NULL DEFAULT NULL AFTER `IdDeleted`;";
            try {
                $this->db->createCommand($sql)->execute();
                echo "поле OrderTo успешно добавлено";
                return true;

            } catch(\Exception $e) {

            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = "SHOW COLUMNS FROM `order_pay` LIKE 'OrderTo'";
        // vdump($this->db->createCommand($sql)->query()->count());
        if($this->db->createCommand($sql)->query()->count()) {
            $sql = "ALTER TABLE `order_pay` DROP COLUMN `OrderTo`;";
            try {
                $this->db->createCommand($sql)->execute();
                echo "поле OrderTo успешно удалено";
                return true;

            } catch(\Exception $e) {

            }
        }

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200901_123817_VPBC_142 cannot be reverted.\n";

        return false;
    }
    */
}
