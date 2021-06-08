<?php

use app\services\payment\models\PaySchet;
use yii\db\Migration;

/**
 * Class m210527_064945_add_operations_in_pay_schets
 */
class m210527_064945_add_operations_in_pay_schets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            PaySchet::tableName(),
            'Operations',
            $this->text()->after('PayType')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(PaySchet::tableName(), 'Operations');
        return true;
    }
}
