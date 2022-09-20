<?php

namespace app\services\payment\banks\results\asc;

/**
 * @property-read string $url
 * @property-read array $parameters POST-parameters.
 */
final class AcsRedirectPostResult extends BaseAcsResult
{
    public function __construct(string $url, array $parameters)
    {
        parent::__construct(get_defined_vars());
    }
}