<?php

use yii\db\Migration;

/**
 * Class m200305_062916_addparetneroptionvozn
 */
class m200305_062916_addparetneroptionvozn extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('partner', 'VoznagVyplatDirect', $this
            ->tinyInteger(1)
            ->unsigned()
            ->notNull()
            ->defaultValue(0)
            ->comment('voznag po vyplatam 0 - oplata po schety cheta 1 - vyvod so scheta')
        );

        $this->addColumn('partner','LoginTkbOctVyvod','varchar(40) DEFAULT NULL');
        $this->addColumn('partner','KeyTkbOctVyvod','varchar(300) DEFAULT NULL');
        $this->addColumn('partner','LoginTkbOctPerevod','varchar(40) DEFAULT NULL');
        $this->addColumn('partner','KeyTkbOctPerevod','varchar(300) DEFAULT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('partner','VoznagVyplatDirect');

        $this->dropColumn('partner','LoginTkbOctVyvod');
        $this->dropColumn('partner','KeyTkbOctVyvod');
        $this->dropColumn('partner','LoginTkbOctPerevod');
        $this->dropColumn('partner','KeyTkbOctPerevod');

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200305_062916_addparetneroptionvozn cannot be reverted.\n";

        return false;
    }
    */
}
