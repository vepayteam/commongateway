<?php

namespace app\services\balance\traits;

use app\models\bank\TCBank;
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
        $account = null;
        if ($bank->ID === TCBank::$bank) {
            $account = $this->partner->SchetTcb;
        }
        $getBalanceRequest = new GetBalanceRequest();
        $getBalanceRequest->currency = 'RUB'; //TODO: add dynamic currency requests
        $getBalanceRequest->account = $account;
        return $getBalanceRequest;
    }
}
