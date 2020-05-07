<?php

use yii\db\Migration;

/**
 * Class m200507_090708_draftcomis
 */
class m200507_090708_draftcomis extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('drafts', 'SummComis', $this->integer(10)->unsigned()->defaultValue(0)->notNull()->after('SummNoNds'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('drafts', 'SummComis');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200507_090708_draftcomis cannot be reverted.\n";

        return false;
    }
    */
}
