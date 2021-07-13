<?php

use app\services\payment\models\Currency;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%payshet}}`.
 */
class m210524_122555_add_client_and_currency_columns_to_payshet_table extends Migration
{

    private const NEW_STRING_COLUMNS = [
        'AddressUser',
        'LoginUser',
        'PhoneUser',
        'ZipUser'
    ];

    private const CURRENCY_ID_KEY = 'CurrencyId';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $defaultCurrency = Currency::find()
            ->select(['Id'])
            ->where([
                'Code' => Currency::MAIN_CURRENCY
            ])
            ->one();

        foreach (self::NEW_STRING_COLUMNS as $column) {
            $length = 255;
            switch ($column) {
                case "LoginUser":
                case "PhoneUser":
                    $length = 32;
                    break;
                case "ZipUser":
                    $length = 16;
                    break;
            }
            $this->createStringColumn($column, $length);
        }
        $this->addColumn(
            PaySchet::tableName(),
            self::CURRENCY_ID_KEY,
            $this->integer()
                ->unsigned()
                ->notNull()
                ->defaultValue($defaultCurrency->Id)
        );

        $gates = new PartnerBankGate();
        if ($gates->hasAttribute(self::CURRENCY_ID_KEY)) {
            $this->dropColumn(PartnerBankGate::tableName(), self::CURRENCY_ID_KEY);
        }

        if (!$gates->hasAttribute(self::CURRENCY_ID_KEY)) {
            $this->addColumn(
                PartnerBankGate::tableName(),
                self::CURRENCY_ID_KEY,
                $this->integer()
                    ->unsigned()
                    ->notNull()
                    ->defaultValue($defaultCurrency->Id)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $paySchet = new PaySchet();
        $gates = new PartnerBankGate();
        foreach (self::NEW_STRING_COLUMNS as $column) {
            if ($paySchet->hasAttribute($column)) {
                $this->dropColumn(PaySchet::tableName(), $column);
            }
        }
        if ($paySchet->hasAttribute(self::CURRENCY_ID_KEY)) {
            $this->dropColumn(PaySchet::tableName(), self::CURRENCY_ID_KEY);
        }
        if ($gates->hasAttribute(self::CURRENCY_ID_KEY)) {
            $this->dropColumn(PartnerBankGate::tableName(), self::CURRENCY_ID_KEY);
        }
    }

    private function createStringColumn($column, $length)
    {
        $this->addColumn(
            PaySchet::tableName(),
            $column,
            $this->string($length)
                ->null()
                ->after('CityUser')
        );
    }
}
