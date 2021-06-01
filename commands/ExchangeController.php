<?php

namespace app\commands;

use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\WalletoBankAdapter;
use app\services\payment\models\Bank;
use app\services\payment\models\CurrencyExchange;
use Carbon\Carbon;
use Yii;
use yii\console\Controller;

class ExchangeController extends Controller
{
    static $arr = ['RUB', 'USD', 'EUR'];

    public function actionUpdate()
    {
        /** @var Bank $bank */
        $bank = Bank::find()
            ->where(['ID' => WalletoBankAdapter::$bank])
            ->one();
        if (!$bank) {
            Yii::warning('Exchange command: Банк ' . WalletoBankAdapter::$bank . ' не найден');
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $adapter = $bankAdapterBuilder->buildByBankOnly($bank)->getBankAdapter();
        $rates = $adapter->currencyExchangeRates();

        foreach ($rates->exchangeRates as $currencyRate) {
            $from = $currencyRate['from'];
            $to = $currencyRate['to'];
            $rate = floatval($currencyRate['rate']); // TODO check rate

            if (!in_array($from, self::$arr) || !in_array($to, self::$arr)) {
                continue;
            }

            $lastCurrencyRate = CurrencyExchange::getLastRate($from, $to);
            if ($lastCurrencyRate !== null && $lastCurrencyRate->Rate === $rate) {
                // TODO log etc
                continue;
            }

            $record = new CurrencyExchange();
            $record->BankId = $bank->ID;
            $record->From = $from;
            $record->To = $to;
            $record->Rate = $rate;
            $record->CreatedAt = Carbon::now();
            $record->save();
        }
    }
}
