<?php

use app\models\parsers\AllAsn;
use yii\db\Migration;

/**
 * Class m200127_081638_parse_asn
 */
class m200127_081638_parse_asn extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $parser = new AllAsn("RU");
        $parser->saveToDb();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->truncateTable('antifraud_asn');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200127_081638_parse_asn cannot be reverted.\n";

        return false;
    }
    */
}
