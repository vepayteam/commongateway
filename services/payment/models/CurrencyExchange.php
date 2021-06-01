<?php

namespace app\services\payment\models;

use Carbon\Carbon;
use yii\db\ActiveRecord;

/**
 * @property int Id
 * @property int BankId
 * @property string From
 * @property string To
 * @property float Rate
 * @property Carbon CreatedAt
 */
class CurrencyExchange extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'currency_exchanges';
    }

    public function rules()
    {
        return [
            ['BankId', 'integer'],
            [['From', 'To'], 'string', 'length' => [1, 5]],
            ['Rate', 'double'],
        ];
    }

    public function getBank()
    {
        return $this->hasOne(Bank::class, ['ID' => 'BankId']);
    }

    public static function getLastRate(string $from, string $to): ?CurrencyExchange {
        /** @var CurrencyExchange $currencyExchange */
        $currencyExchange = self::find()
            ->where([
                'From' => $from,
                'To' => $to,
            ])
            ->orderBy('CreatedAt DESC')
            ->one();

        return $currencyExchange;
    }
}
