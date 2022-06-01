<?php

use app\services\payment\models\Currency;
use yii\db\Migration;

/**
 * Class m220601_092536_add_azn_currency_to_currency_table
 */
class m220601_092536_add_azn_currency_to_currency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $currency = new Currency([
            'Name' => 'Azerbaijanian Manat',
            'Code' => 'AZN',
            'Number' => 944,
        ]);
        $currency->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Currency::deleteAll([
            'Number' => 944,
        ]);
    }
}
