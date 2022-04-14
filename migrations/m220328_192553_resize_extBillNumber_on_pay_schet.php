<?php

use app\services\payment\models\PaySchet;
use yii\db\Migration;

/**
 * Class m220328_192553_resize_extBillNumber_on_pay_schet
 */
class m220328_192553_resize_extBillNumber_on_pay_schet extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(PaySchet::tableName(), 'ExtBillNumber', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn(PaySchet::tableName(), 'ExtBillNumber', $this->string(50));
        return true;
    }
}
