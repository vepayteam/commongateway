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
        $this->insert('currency', [
            'Name' => 'Azerbaijanian Manat',
            'Code' => 'AZN',
            'Number' => 944,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('currency', [
            'Number' => 944,
        ]);
    }
}
