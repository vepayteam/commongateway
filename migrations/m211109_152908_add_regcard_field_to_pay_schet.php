<?php

use yii\db\Migration;

/**
 * Class m211109_152908_add_regcard_field_to_pay_schet
 */
class m211109_152908_add_regcard_field_to_pay_schet extends Migration
{
    public function up()
    {
        $this->addColumn(
            'pay_schet',
            'RegisterCard',
            $this->boolean()
                ->notNull()
                ->defaultValue(0)
                ->comment('Регистрировать ли карту для рекуррентных платежей при оплате.')
        );
    }

    public function down()
    {
        $this->dropColumn('pay_schet', 'RegisterCard');
    }
}
