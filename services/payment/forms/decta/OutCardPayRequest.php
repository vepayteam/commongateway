<?php

namespace app\services\payment\forms\decta;

use app\services\base\Structure;
use app\services\payment\forms\decta\payout\Client;

class OutCardPayRequest extends Structure
{
    /**
     * @var string $terminal_processing_id
     */
    public $account;
    /**
     * @var string $terminal_processing_id
     */
    public $terminal_processing_id;
    /**
     * @var Client $client
     */
    public $client;
    /**
     * @var string $sender_name
     */
    public $sender_name;
    /**
     * @var string $sdwo_merchant_id
     */
    public $sdwo_merchant_id;
    /**
     * @var float $amount
     */
    public $amount;
    /**
     * @var string $currency
     */
    public $currency;
    /**
     * @var string $language
     */
    public $language;
    /**
     * @var string $description
     */
    public $description;
    /**
     * @var bool $is_test
     */
    public $is_test;
    /**
     * @var string $number
     */
    public $number;
    /**
     * @var int $max_payment_attempts
     */
    public $max_payment_attempts;
}
