<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class CardGetTokenRequest extends BaseObject
{
    /**
     * @var string bankcard's number
     */
    private $number;

    /**
     * @var int bankcard's expiration month, with or without leading zero
     */
    private $expiration_month;

    /**
     * @var int bankcard's expiration year (4 digits)
     */
    private $expiration_year;

    /**
     * @var string bankcard's security code (CVC, CVV2, BATCH)
     */
    private $security_code;

    /**
     * @param string $number
     * @param int $expiration_month
     * @param int $expiration_year
     * @param string $security_code
     */
    public function __construct(
        string $number,
        int    $expiration_month,
        int    $expiration_year,
        string $security_code
    )
    {
        parent::__construct();

        $this->number = $number;
        $this->expiration_month = $expiration_month;
        $this->expiration_year = $expiration_year;
        $this->security_code = $security_code;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getExpirationMonth(): int
    {
        return $this->expiration_month;
    }

    /**
     * @return int
     */
    public function getExpirationYear(): int
    {
        return $this->expiration_year;
    }

    /**
     * @return string
     */
    public function getSecurityCode(): string
    {
        return $this->security_code;
    }
}