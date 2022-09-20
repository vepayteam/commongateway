<?php

namespace app\services\payment\banks\results;

use app\services\payment\banks\results\asc\BaseAcsResult;

/**
 * @property-read string $bankTransactionId Transaction ID returned by bank.
 * @property-read BaseAcsResult|null $acs
 */
final class P2pOkResult extends BaseP2pResult
{
    public function __construct(string $bankTransactionId, ?BaseAcsResult $acs = null)
    {
        parent::__construct(get_defined_vars());
    }
}