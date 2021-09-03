<?php

namespace app\services;

use app\models\bank\Banks;
use app\services\compensationService\CompensationException;
use app\services\payment\models\Currency;
use app\services\payment\models\CurrencyExchange;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Carbon\Carbon;
use yii\base\Component;

/**
 * Сервис для расчета отчислений.
 */
class CompensationService extends Component
{

    /**
     * @param Banks $bank
     * @param Currency $currencyFrom
     * @param Currency $currencyTo
     * @return float
     * @throws CompensationException
     * @todo Убрать в отдельный сервис.
     */
    private function getCurrencyRate(Banks $bank, Currency $currencyFrom, Currency $currencyTo): float
    {
        if ($currencyFrom->Id === $currencyTo->Id) {
            return 1.0;
        }

        /** @var CurrencyExchange $currencyExchange */
        $currencyExchange = CurrencyExchange::find()
            ->andWhere([
                'BankId' => $bank->ID,
                'From' => $currencyFrom->Code,
                'To' => $currencyTo->Code,
            ])
            ->andWhere(['=', 'DATE(RateFrom)', (new Carbon())->format('Y-m-d')])
            ->orderBy('CreatedAt DESC')
            ->one();

        if ($currencyExchange === null) {
            $message = "Currency exchange rate not found (bank={$bank->ID}, {$currencyFrom->Code} => {$currencyTo->Code}).";
            \Yii::error(__CLASS__ . ': ' . $message);
            throw new CompensationException($message, CompensationException::NO_EXCHANGE_RATE);
        }

        return $currencyExchange->Rate;
    }

    /**
     * Размер компенсации от контрагента (партнера/мерчанта).
     *
     * @param PaySchet $paySchet
     * @param PartnerBankGate $gate
     * @return float
     * @throws CompensationException
     */
    public function calculateForPartner(PaySchet $paySchet, PartnerBankGate $gate): float
    {
        $amount = $paySchet->SummPay;

        /**
         * Используем шлюз или услугу в качестве источника значений для подсчета компенсации.
         * Значения, хранящиеся в рублях, умножаем на сто, т.к. считаем в копейках.
         */
        if ($gate->UseGateCompensation) {
            $feeCurrencyRate = $this->getCurrencyRate($gate->bank, $gate->feeCurrency, $gate->currency);
            $minimalFeeCurrencyRate = $this->getCurrencyRate($gate->bank, $gate->minimalFeeCurrency, $gate->currency);

            $commission = $gate->PartnerCommission ?? 0.0;
            $fee = ($gate->PartnerFee ?? 0.0) * 100.0 * $feeCurrencyRate;
            $minimalFee = ($gate->PartnerMinimalFee ?? 0.0) * 100.0 * $minimalFeeCurrencyRate;
        } else {
            $commission = $paySchet->uslugatovar->ProvVoznagPC;
            $fee = 0;
            $minimalFee = $paySchet->uslugatovar->ProvVoznagMin * 100.0;
        }

        return $this->calculate($amount, $commission, $fee, $minimalFee);
    }

    /**
     * Размер компенсации от клиента (пользователя, оплачивающего счет).
     *
     * @param PaySchet $paySchet
     * @param PartnerBankGate $gate
     * @return float
     * @throws CompensationException
     */
    public function calculateForClient(PaySchet $paySchet, PartnerBankGate $gate): float
    {
        $amount = $paySchet->SummPay;

        /**
         * Используем шлюз или услугу в качестве источника значений для подсчета компенсации.
         * Значения, хранящиеся в рублях, умножаем на сто, т.к. считаем в копейках.
         */
        if ($gate->UseGateCompensation) {
            $feeCurrencyRate = $this->getCurrencyRate($gate->bank, $gate->feeCurrency, $gate->currency);
            $minimalFeeCurrencyRate = $this->getCurrencyRate($gate->bank, $gate->minimalFeeCurrency, $gate->currency);

            $commission = $gate->ClientCommission ?? 0.0;
            $fee = ($gate->ClientFee ?? 0.0) * 100.0 * $feeCurrencyRate;
            $minimalFee = ($gate->ClientMinimalFee ?? 0.0) * 100.0 * $minimalFeeCurrencyRate;
        } else {
            $commission = $paySchet->uslugatovar->PcComission;
            $fee = 0;
            $minimalFee = $paySchet->uslugatovar->MinsumComiss * 100.0;
        }

        return $this->calculate($amount, $commission, $fee, $minimalFee);
    }

    /**
     * Размер компенсации банку в копейках.
     *
     * @param PaySchet $paySchet
     * @param PartnerBankGate $gate
     * @return float
     * @throws CompensationException
     */
    public function calculateForBank(PaySchet $paySchet, PartnerBankGate $gate): float
    {
        // Считаем от общей суммы, уплаченной клиентом
        $amount = intval(round($paySchet->SummPay + round($this->calculateForClient($paySchet, $gate))));

        /**
         * Используем шлюз или услугу в качестве источника значений для подсчета компенсации.
         * Значения, хранящиеся в рублях, умножаем на сто, т.к. считаем в копейках.
         */
        if ($gate->UseGateCompensation) {
            $feeCurrencyRate = $this->getCurrencyRate($gate->bank, $gate->feeCurrency, $gate->currency);
            $minimalFeeCurrencyRate = $this->getCurrencyRate($gate->bank, $gate->minimalFeeCurrency, $gate->currency);

            $commission = $gate->BankCommission ?? 0.0;
            $fee = ($gate->BankFee ?? 0.0) * 100.0 * $feeCurrencyRate;
            $minimalFee = ($gate->BankMinimalFee ?? 0.0) * 100.0 * $minimalFeeCurrencyRate;
        } else {
            $commission = $paySchet->uslugatovar->ProvComisPC;
            $fee = 0;
            $minimalFee = $paySchet->uslugatovar->ProvComisMin * 100.0;
        }

        return $this->calculate($amount, $commission, $fee, $minimalFee);
    }

    /**
     * Подсчитывает точное значение компенсации в копейках (центах).
     *
     * @param int $amount Сумма платежа в копейках.
     * @param float $commission Процентная комиссия в копейках.
     * @param float $fee Фиксированная комиссия в копейках.
     * @param float $minimalFee Минимальная комиссия в копейках.
     */
    public function calculate(int $amount, float $commission, float $fee, float $minimalFee): float
    {
        $result = $amount * ($commission / 100.0) + $fee;

        if ($result < $minimalFee) {
            $result = $minimalFee;
        }

        return $result;
    }
}