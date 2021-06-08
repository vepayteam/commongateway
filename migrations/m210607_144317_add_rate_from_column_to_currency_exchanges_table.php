<?php

use app\services\payment\models\CurrencyExchange;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%currency_exchanges}}`.
 */
class m210607_144317_add_rate_from_column_to_currency_exchanges_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            CurrencyExchange::tableName(),
            'RateFrom',
            $this->timestamp()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            CurrencyExchange::tableName(),
            'RateFrom'
        );
    }
}
