<?php

namespace app\services\payment\forms\decta;

use app\services\base\Structure;
use app\services\payment\forms\decta\payin\Client;

class CreatePayRequest extends Structure
{
    /**
     * @var Client $client
     */
    public $client;
    /**
     * @var [] $products
     */
    public $products;
    /**
     * @var string $currency
     */
    public $currency = 'RUB';
    /**
     * @var int $total
     */
    public $total;
    /**
     * @var string $response_type
     */
    public $response_type;
    /**
     * @var string $success_redirect
     */
    public $success_redirect;
    /**
     * @var string $failure_redirect
     */
    public $failure_redirect;
}
