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
    private $expirationMonth;

    /**
     * @var int bankcard's expiration year (4 digits)
     */
    private $expirationYear;

    /**
     * @var string bankcard's security code (CVC, CVV2, BATCH)
     */
    private $securityCode;

    /**
     * @param string $number
     * @param int $expirationMonth
     * @param int $expirationYear
     * @param string $securityCode
     */
    public function __construct(
        string $number,
        int $expirationMonth,
        int $expirationYear,
        string $securityCode
    )
    {
        parent::__construct();

        $this->number = $number;
        $this->expirationMonth = $expirationMonth;
        $this->expirationYear = $expirationYear;
        $this->securityCode = $securityCode;
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
        return $this->expirationMonth;
    }

    /**
     * @return int
     */
    public function getExpirationYear(): int
    {
        return $this->expirationYear;
    }

    /**
     * @return string
     */
    public function getSecurityCode(): string
    {
        return $this->securityCode;
    }
}