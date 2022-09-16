<?php

namespace app\services\payment\banks\results;

/**
 * @property-read string|null $errorMessage
 */
final class P2pErrorResult extends BaseP2pResult
{
    public function __construct(?string $errorMessage = null)
    {
        parent::__construct(get_defined_vars());
    }
}