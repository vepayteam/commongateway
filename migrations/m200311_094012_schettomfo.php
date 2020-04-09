<?php

use yii\db\Migration;

/**
 * Class m200311_094012_schettomfo
 */
class m200311_094012_schettomfo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('act_schet', [
            'ID' => $this->primaryKey(10)->unsigned(),
            'IdPartner' => $this->integer(10)->unsigned()->notNull(),
            'IdAct' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
            'NumSchet' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
            'SumSchet' => $this->bigInteger(20)->notNull()->defaultValue(0),
            'DateSchet' => $this->integer(10)->unsigned()->notNull()->defaultValue(0),
            'Komment' => $this->string(255),
            'IsDeleted' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
        ]);
        $this->createIndex('IdPartner_idx', 'act_schet', 'IdPartner');
        $this->createIndex('IdAct_idx', 'act_schet', 'IdAct');
        $this->createIndex('DateSchet_idx', 'act_schet', 'DateSchet');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('act_schet');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200311_094012_schettomfo cannot be reverted.\n";

        return false;
    }
    */
}
