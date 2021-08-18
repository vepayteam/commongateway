<?php

namespace app\services\exchange_rates;

use app\services\exchange_rates\jobs\WallettoExchangeRateJob;
use app\services\exchange_rates\models\ExchangeRateUpdateResult;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CurrencyExchangeRatesResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\IBankAdapter;
use app\services\payment\banks\WallettoBankAdapter;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\Bank;
use app\services\payment\models\Currency;
use app\services\payment\models\CurrencyExchange;
use Carbon\Carbon;
use Exception;
use Yii;

class WallettoExchangeRateService
{
    const MAX_TRY_COUNT = 60;

    public function update(int $tryCount = 1)
    {
        if ($tryCount >= self::MAX_TRY_COUNT) {
            Yii::warning('WallettoExchangeRateService: не удалось загрузить курсы валют');
            return;
        }

        Yii::warning('WallettoExchangeRateService: загружаем курсы валют попытка ' . $tryCount);

        try {
            $result = $this->loadRates();
        } catch (Exception $e) {
            Yii::warning('WallettoExchangeRateService err: ' . $e->getMessage());
            $this->pushToQueue($tryCount);

            return;
        }

        if ($result->status !== ExchangeRateUpdateResult::STATUS_DONE) {
            Yii::warning($result->error);
            $this->pushToQueue($tryCount);

            return;
        }

        if ($result->rateCount === 0) {
            Yii::warning('WallettoExchangeRateService: нет доступных курсов');
            $this->pushToQueue($tryCount);
        }
    }

    private function pushToQueue(int $tryCount)
    {
        Yii::$app->queue->delay(60)->push(new WallettoExchangeRateJob([
            'tryCount' => $tryCount + 1,
        ]));
    }

    private function loadRates(): ExchangeRateUpdateResult
    {
        $bank = $this->getBank();
        if (!$bank) {
            return ExchangeRateUpdateResult::setError('WallettoExchangeRateService: Банк ' . WallettoBankAdapter::$bank . ' не найден');
        }

        $adapter = $this->getAdapter($bank);
        $rates = $adapter->currencyExchangeRates();
        if ($rates->status != BaseResponse::STATUS_DONE) {
            return ExchangeRateUpdateResult::setError('WallettoExchangeRateService: ' . $rates->message);
        }

        return $this->insertRates($bank, $rates);
    }

    private function insertRates(Bank $bank, CurrencyExchangeRatesResponse $rates): ExchangeRateUpdateResult
    {
        $currencyCodes = Currency::getCurrencyCodes();

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
                    'WallettoExchangeRateService: ' . $from . ' -> ' . $to . ' не поменялся с прошлого раза rate ' . $rate
                );
                continue;
            }

            $record = new CurrencyExchange();
            $record->BankId = $bank->ID;
            $record->From = $from;
            $record->To = $to;
            $record->Rate = $rate;
            $record->CreatedAt = Carbon::now();
            $record->RateFrom = $this->getRateFrom();
            $record->save();

            $inserted++;
        }

        Yii::warning('WallettoExchangeRateService: добавлено новых записей ' . $inserted);

        return ExchangeRateUpdateResult::setDone($inserted, count($rates->exchangeRates));
    }

    private function getAdapter(Bank $bank): ?IBankAdapter
    {
        try {
            $bankAdapterBuilder = new BankAdapterBuilder();
            return $bankAdapterBuilder->buildByBankOnly($bank)->getBankAdapter();
        } catch (GateException $e) {
            Yii::warning('WallettoExchangeRateService: ' . $e->getMessage());
        }

        return null;
    }

    private function getBank(): ?Bank
    {
        /** @var Bank $bank */
        $bank = Bank::find()
            ->where(['ID' => WallettoBankAdapter::$bank])
            ->one();

        return $bank;
    }

    private function getRateFrom(): Carbon
    {
        return Carbon::now(WallettoBankAdapter::BANK_TIMEZONE)->startOfDay();
    }
}
