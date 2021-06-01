<?php

namespace app\commands;

use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\WalletoBankAdapter;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\Bank;
use app\services\payment\models\Currency;
use app\services\payment\models\CurrencyExchange;
use Carbon\Carbon;
use Yii;
use yii\console\Controller;

class ExchangeController extends Controller
{
    public function actionUpdate()
    {
        /** @var Bank $bank */
        $bank = Bank::find()
            ->where(['ID' => WalletoBankAdapter::$bank])
            ->one();
        if (!$bank) {
            Yii::warning('Exchange update: Банк ' . WalletoBankAdapter::$bank . ' не найден');
        }

        $currencyCodes = Currency::getCurrencyCodes();

        try {
            $bankAdapterBuilder = new BankAdapterBuilder();
            $adapter = $bankAdapterBuilder->buildByBankOnly($bank)->getBankAdapter();
        } catch (GateException $e) {
            Yii::warning('Exchange update: ' . $e->getMessage());
            return;
        }

        $rates = $adapter->currencyExchangeRates();

        $inserted = 0;
        foreach ($rates->exchangeRates as $exchangeRate) {
            $from = $exchangeRate['from'];
            $to = $exchangeRate['to'];
            $rate = floatval($exchangeRate['rate']);

            if (!in_array($from, $currencyCodes) || !in_array($to, $currencyCodes)) {
                continue;
            }

            $lastExchangeRate = CurrencyExchange::getLastRate($from, $to);
            if ($lastExchangeRate !== null && $lastExchangeRate->Rate === $rate) {
                Yii::warning(
                    'Exchange update: ' . $from . ' -> ' . $to . ' не поменялся с прошлого раза rate ' . $rate
                );
                continue;
            }

            $record = new CurrencyExchange();
            $record->BankId = $bank->ID;
            $record->From = $from;
            $record->To = $to;
            $record->Rate = $rate;
            $record->CreatedAt = Carbon::now();
            $record->save();

            $inserted++;
        }

        Yii::warning('Exchange update: добавлено новых записей ' . $inserted);
    }
}
