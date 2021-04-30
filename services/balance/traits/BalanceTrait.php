<?php

namespace app\services\balance\traits;

use app\models\bank\TCBank;
use app\models\payonline\Partner;
use app\services\balance\Balance;
use app\services\balance\response\BalanceResponse;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;

trait BalanceTrait
{

    /** @var Partner $partner */
    public $partner;

    public function getActiveBankGates(): array
    {
        return $this->partner
            ->getAllEnabledPartnerBankGatesByColumnDistinct([
                    'BankId', //todo remove?
                    'SchetNumber',
                    'Login',
            ]);
    }

    /**
     * @param $bank
     * @param $activeGate
     * @return GetBalanceRequest
     */
    public function formatRequest($bank, $activeGate): GetBalanceRequest
    {
        $accounts = [];
        if ($bank->ID === TCBank::$bank) {
            //todo: change to gate settings
            $accounts = [
                Balance::BALANCE_TYPE_PAY_OUT => $this->partner->SchetTcb,
                Balance::BALANCE_TYPE_NOMINAL => $this->partner->SchetTcbNominal,
                Balance::BALANCE_TYPE_PAY_IN => $this->partner->SchetTcbTransit,
            ];
            // Если счета: SchetTcb и SchetTcbTransit совпадают то выводим только счет на выдачу
            if (
                !empty($this->partner->SchetTcbTransit)
                && $this->partner->SchetTcb === $this->partner->SchetTcbTransit
            ) {
                unset($accounts[Balance::BALANCE_TYPE_PAY_IN]);
            }
        }
        $getBalanceRequest = new GetBalanceRequest();
        $getBalanceRequest->currency = 'RUB'; //TODO: add dynamic currency requests
        $getBalanceRequest->bankName = $bank->getName();
        $getBalanceRequest->accounts = array_filter($accounts);
        return $getBalanceRequest;
    }
}
