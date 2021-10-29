<?php

use app\services\payment\models\PaySchet;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%pay_schet}}`.
 */
class m211028_154811_add_regcard_column_to_pay_schet_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(PaySchet::tableName(), 'regcard', $this->tinyInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(PaySchet::tableName(), 'regcard');
    }
}