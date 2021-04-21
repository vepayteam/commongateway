<?php

namespace app\services\balance\traits;

use app\services\balance\response\BalanceResponse;

trait BalanceTrait
{
    /**
     * @param string $errorMsg
     * @return \app\services\balance\response\BalanceResponse
     */
    public function balanceError(string $errorMsg): BalanceResponse
    {
        $response = new BalanceResponse();
        $response->status = BalanceResponse::STATUS_ERROR;
        $response->message = $errorMsg;
        $response->hasError = true;
        return $response;
    }
}
