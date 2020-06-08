<?php

use yii\db\Migration;

/**
 * Class m200603_061159_otchetps
 */
class m200603_061159_otchetps extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('otchetps', [
            'ID' => $this->primaryKey()->unsigned(),
            'IdPartner' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
            'DateFrom' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
            'DateTo' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
            'OstBeg' => $this->bigInteger(20)->notNull()->defaultValue(0),
            'OstEnd' => $this->bigInteger(20)->notNull()->defaultValue(0),
            'Pogashen' => $this->bigInteger(20)->notNull()->defaultValue(0),
            'Vedacha' => $this->bigInteger(20)->notNull()->defaultValue(0),
            'Popolnen' => $this->bigInteger(20)->notNull()->defaultValue(0),
            'Perechislen' => $this->bigInteger(20)->notNull()->defaultValue(0),
            'ProchspisanPogas' => $this->bigInteger(20)->notNull()->defaultValue(0),
            'ProchspisanVydach' => $this->bigInteger(20)->notNull()->defaultValue(0),
        ],'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
        $this->createIndex('period_idx', 'otchetps', ['IdPartner', 'DateFrom', 'DateTo'],true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('otchetps');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200603_061159_otchetps cannot be reverted.\n";

        return false;
    }
    */
}
