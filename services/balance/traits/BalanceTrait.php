<?php

namespace app\services\balance\traits;

use app\models\bank\TCBank;
use app\services\balance\Balance;
use app\services\balance\response\BalanceResponse;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;

trait BalanceTrait
{
    /**
     * @param string $errorMsg
     * @return BalanceResponse
     */
    public function balanceError(string $errorMsg): BalanceResponse
    {
        $response = new BalanceResponse();
        $response->status = BalanceResponse::STATUS_ERROR;
        $response->message = $errorMsg;
        return $response;
    }

    /**
     * @param $bank
     * @return GetBalanceRequest
     */
    public function formatRequest($bank): GetBalanceRequest
    {
        $accounts = [];
        if ($bank->ID === TCBank::$bank) {
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
