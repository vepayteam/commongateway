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

    /**
     * TODO: change request to orderBy
     * @return array
     */
    public function getActiveBankGates(): array
    {
        $allActiveBankGates = [];
        $uniqueActiveGates = $this->partner
            ->getEnabledBankGates()
            ->select([
                'SchetNumber',
                'Login',
            ])
            ->where(['Enable' => 1])
            ->distinct()
            ->all();

        if (!$uniqueActiveGates) {
            return [];
        }
        foreach ($uniqueActiveGates as $activeGate) {
            $allActiveBankGates[] = $this->partner
                ->getEnabledBankGates()
                ->select([
                    'TU',
                    'SchetType',
                    'SchetNumber',
                    'Login',
                    'BankId',
                ])
                ->where([
                    'Enable' => 1,
                    'SchetNumber' => $activeGate->SchetNumber,
                    'Login' => $activeGate->Login,
                ])->one();
        }

        return $allActiveBankGates;
    }

    /**
     * @param PartnerBankGate $activeGate
     * @param $bank
     * @return GetBalanceRequest
     */
    public function formatRequest(PartnerBankGate $activeGate, $bank): GetBalanceRequest
    {
        /** @var AccountTypes */
        $accountType = $activeGate->SchetType;
        $accountNumber = $activeGate->SchetNumber ?? null;
        $getBalanceRequest = new GetBalanceRequest();
        $getBalanceRequest->currency = 'RUB'; //TODO: add dynamic currency requests
        $getBalanceRequest->bankName = $bank->getName();
        $getBalanceRequest->accountNumber = $accountNumber;
        $getBalanceRequest->accountType = $accountType;
        $getBalanceRequest->uslugatovarType = (int)$activeGate->TU;
        return $getBalanceRequest;
    }

    public function getCacheKeyPrefix(): string
    {
        return self::BALANCE_CACHE_PREFIX . $this->partner->ID;
    }
}
