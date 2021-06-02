<?php

use app\services\payment\models\Currency;
use app\services\payment\models\PartnerBankGate;
use yii\db\Migration;

/**
 * Class m210505_114919_create_currency_table_and_add_currency_to_bank_gates
 */
class m210505_114919_create_currency_table_and_add_currency_to_bank_gates extends Migration
{
    /**
     * https://en.wikipedia.org/wiki/ISO_4217
     * https://www.cbr-xml-daily.ru/
     */
    private const CURRENCY = [
        [
            'name' => 'Russian Ruble',
            'number' => 643,
            'code' => 'RUB',
        ],
        [
            'name' => 'US Dollar',
            'number' => 840,
            'code' => 'USD'
        ],
        [
            'name' => 'Euro',
            'number' => 978,
            'code' => 'EUR'
        ]
    ];
    private const COLUMN = 'CurrencyId';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->getTableSchema('currency', true) === null) {
            $this->createTable(Currency::tableName(), [
                'Id' => $this->primaryKey(),
                'Name' => $this->string()->notNull(),
                'Code' => $this->string(3)->notNull(),
                'Number' => $this->integer(3)->notNull(),
            ]);
        }

        foreach (self::CURRENCY as $primaryCurrency) {
            $currencyRow = new Currency();
            $currencyRow->Name = $primaryCurrency['name'];
            $currencyRow->Number = $primaryCurrency['number'];
            $currencyRow->Code = $primaryCurrency['code'];
            $currencyRow->save(false);
        }

        $gates = new PartnerBankGate();
        if (!$gates->hasAttribute(self::COLUMN)) {
            $this->addColumn(
                PartnerBankGate::tableName(),
                self::COLUMN,
                $this->integer()
                    ->notNull()
                    ->defaultValue(0)
                    ->after('BankId')
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $gates = new PartnerBankGate();
        if ($gates->hasAttribute(self::COLUMN)) {
            $this->dropColumn(PartnerBankGate::tableName(), self::COLUMN);
        }
    }
}
