<?php

namespace app\services\payment\banks\structures;

use app\services\base\Structure;

/**
 * Class StatementResult
 *
 * @property int    $status
 * @property string $message
 * @property array  $statements
 *
 * @package app\services\payment\banks\structures
 */
class StatementResult extends Structure
{
    public $status;
    public $message    = '';
    public $statements = [];

    public const STATUS_FAILED = 0;
    public const STATUS_OK     = 1;
}
