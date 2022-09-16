<?php

namespace app\services\payment\banks\results\asc;

/**
 * @property-read string $url
 */
final class AcsRedirectGetResult extends BaseAcsResult
{
    public function __construct(string $url)
    {
        parent::__construct(get_defined_vars());
    }
}