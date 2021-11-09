<?php

use yii\db\Migration;

/**
 * Class m211109_152908_add_regcard_field_to_pay_schet
 */
class m211109_152908_add_regcard_field_to_pay_schet extends Migration
{
    public function up()
    {
        $this->addColumn('pay_schet', 'regcard', $this->boolean()->notNull()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('pay_schet', 'regcard');
    }
}
