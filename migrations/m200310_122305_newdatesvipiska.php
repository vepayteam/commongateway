<?php

use yii\db\Migration;

/**
 * Class m200310_122305_newdatesvipiska
 */
class m200310_122305_newdatesvipiska extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('statements_account','DateDoc', $this->integer(10)->unsigned()->notNull()->defaultValue(0));

        $this->createIndex('DateDoc_idx', 'statements_account', ['DateDoc', 'IdPartner', 'TypeAccount']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('DateDoc_idx','statements_account');

        $this->dropColumn('statements_account','DateDoc');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200310_122305_newdatesvipiska cannot be reverted.\n";

        return false;
    }
    */
}
