<?php

namespace app\services\payment\forms\decta;

use app\services\base\Structure;

/**
 * Class CreatePaySecondStepRequest
 */
class CreatePaySecondStepRequest extends Structure
{
    /**
     * @var string $cardholder_name
     */
    public $cardholder_name;
    /**
     * @var string $card_number
     */
    public $card_number;
    /**
     * @var int $exp_month
     */
    public $exp_month;
    /**
     * @var int $exp_year
     */
    public $exp_year;
    /**
     * @var string $csc
     */
    public $csc;
    /**
     * @var bool $save_card
     */
    public $save_card = false;
    /**
     * @var bool $saved_by_merchant
     */
    public $saved_by_merchant = false;
}
