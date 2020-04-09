<?php

use app\models\antifraud\tables\AFRuleInfo;
use yii\db\Migration;

/**
 * Class m200217_103257_add_rulle_name
 */
class m200217_103257_add_rulle_name extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $record = new AFRuleInfo();
        $record->rule = 'RefundCard';
        $record->description = 'Если в одну дату будет совершено 2 одинаковых выплаты на карту, то вторая из них будет заблокированна.';
        $record->rule_title = 'Блокировка дублирующихся платежей-выплат';
        $record->critical_value = 'Если вторая транзакция была совершена в тот же день что и первая, или с 00:00 по 05:00 следующего дня, на одну и туже сумму, от одного и того же партнера, на одну и туже карту.';
        $record->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return AFRuleInfo::deleteAll(['rule'=>'RefundCard']);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200217_103257_add_rulle_name cannot be reverted.\n";

        return false;
    }
    */
}
