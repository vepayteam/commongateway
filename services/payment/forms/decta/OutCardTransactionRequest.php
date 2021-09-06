<?php

namespace app\services\payment\forms\decta;

use app\services\base\Structure;

/**
 * Class OutCardTransactionRequest
 *
 * @package app\services\payment\forms\decta
 */
class OutCardTransactionRequest extends Structure
{
    /**
     * @var string $card_number2
     */
    public $card_number2;
    /**
     * @var int $exp_month2
     */
    public $exp_month2;
    /**
     * @var int $exp_year2
     */
    public $exp_year2;
    /**
     * @var string $payment_cardholder_name
     */
    public $payment_cardholder_name;
    /**
     * @var bool $save_card
     */
    public $save_card;
    /**
     * @var string $ip_address
     */
    public $ip_address;
}
