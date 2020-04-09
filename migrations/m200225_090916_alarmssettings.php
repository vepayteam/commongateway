<?php

use yii\db\Migration;

/**
 * Class m200225_090916_alarmssettings
 */
class m200225_090916_alarmssettings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('alarms_settings', [
            'ID' => $this->primaryKey()->unsigned(),
            'TypeAlarm' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('tip opoveshenia')->unique(),
            'TimeAlarm' => $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('nastroika minut'),
            'EmailAlarm' => $this->string(250)->comment('email dlia opoveschenia'),
        ],'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->createTable('alarms_send', [
            'ID' => $this->primaryKey()->unsigned(),
            'IdSetting' => $this->integer(10)->unsigned()->notNull()->comment('id alarms_settings'),
            'IdPay' => $this->integer(10)->unsigned()->notNull()->comment('id pay_schet'),
            'TypeSend' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)->comment('tip 0 - otpravka ob oshibke 1 - proverka'),
            'DateSend' => $this->integer(10)->unsigned()->notNull()->defaultValue(0)->comment('data intravki'),
        ],'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
        $this->createIndex('pay_schet_idx', 'alarms_send', ['IdSetting', 'IdPay']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('alarms_settings');
        $this->dropTable('alarms_send');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200225_090916_alarmssettings cannot be reverted.\n";

        return false;
    }
    */
}
