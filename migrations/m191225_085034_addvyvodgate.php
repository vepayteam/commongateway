<?php

use yii\db\Migration;

/**
 * Class m191225_085034_addvyvodgate
 */
class m191225_085034_addvyvodgate extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('banks', 'VyvodBankComis', $this->double());
        $this->addColumn('partner', 'LoginTkbVyvod', $this->string(40)->after('KeyTkbEcom')->comment('vyvod gate'));
        $this->addColumn('partner', 'KeyTkbVyvod', $this->string(300)->after('LoginTkbVyvod'));
        $this->addColumn('partner', 'LoginTkbJkh', $this->string(40)->after('KeyTkbVyvod')->comment('jkh gate'));
        $this->addColumn('partner', 'KeyTkbJkh', $this->string(300)->after('LoginTkbJkh'));
        $this->addColumn('partner', 'SchetTcbTransit', $this->string(40)->after('SchetTcb')->comment('transit schet tcb'));
        $this->addColumn('partner', 'VyvodNaznachen', $this->string(255)->after('KeyTkbVyvod')->comment('naznachenie tpl'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('banks', 'VyvodBankComis');
        $this->dropColumn('partner', 'LoginTkbVyvod');
        $this->dropColumn('partner', 'KeyTkbVyvod');
        $this->dropColumn('partner', 'LoginTkbJkh');
        $this->dropColumn('partner', 'KeyTkbJkh');
        $this->dropColumn('partner', 'SchetTcbTransit');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191225_085034_addvyvodgate cannot be reverted.\n";

        return false;
    }
    */
}
