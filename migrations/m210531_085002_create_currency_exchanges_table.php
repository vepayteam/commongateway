<?php

use app\services\payment\models\CurrencyExchange;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%currency_exchanges}}`.
 */
class m210531_085002_create_currency_exchanges_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(CurrencyExchange::tableName(), [
            'Id' => $this->primaryKey(),
            'BankId' => $this->integer()->unsigned(),
            'From' => $this->string(5),
            'To' => $this->string(5),
            'Rate' => $this->float(),
            'CreatedAt' => $this->timestamp(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(CurrencyExchange::tableName());
    }
}
