<?php

namespace app\services\payment\banks;

use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\CacheValueMissingException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\forms\CreatePaySecondStepForm;

interface IBankSecondStepInterface
{
    /**
     * @param CreatePaySecondStepForm $createPaySecondStepForm
     * @return CreatePayResponse
     * @throws Check3DSv2Exception
     * @throws CacheValueMissingException
     * @throws BankAdapterResponseException
     */
    public function createPayStep2(CreatePaySecondStepForm $createPaySecondStepForm);
}