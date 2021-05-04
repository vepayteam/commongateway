<?php

namespace app\services\balance\traits;

use app\models\bank\TCBank;
use app\models\payonline\Partner;
use app\services\payment\banks\bank_adapter_requests\GetBalanceRequest;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\types\AccountTypes;

trait BalanceTrait
{

    /** @var Partner $partner */
    public $partner;

    public function getActiveBankGates(): array
    {
        return $this->partner
            ->getAllEnabledPartnerBankGatesByColumnDistinct([
                    'SchetNumber',
                    'Login',
            ]);
    }

    /**
     * @param PartnerBankGate $activeGate
     * @param $bank
     * @return GetBalanceRequest
     */
    public function formatRequest(PartnerBankGate $activeGate, $bank): GetBalanceRequest
    {
        $accountNumber = null;
        /** @var AccountTypes */
        $accountType = AccountTypes::TYPE_DEFAULT;
        if ($bank->ID === TCBank::$bank) {
            $accountType = $activeGate->SchetType; //TODO: types check by TU | TovarUslugaType
            $accountNumber = $activeGate->SchetNumber;
        }

        $getBalanceRequest = new GetBalanceRequest();
        $getBalanceRequest->currency = 'RUB'; //TODO: add dynamic currency requests
        $getBalanceRequest->bankName = $bank->getName();
        $getBalanceRequest->accountNumber = $accountNumber;
        $getBalanceRequest->accountType = $accountType;
        return $getBalanceRequest;
    }
}
