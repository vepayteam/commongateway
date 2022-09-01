<?php

namespace app\clients\cauriClient\objects;

use yii\base\BaseObject;

class CardDataObject extends BaseObject
{
    /**
     * @var string last four digits of a bankcard's number
     */
    private $lastFour;

    /**
     * @var string bankcard's number mask. Only first six and last four digits are visible, others are hidden with asterisks.
     */
    private $mask;

    /**
     * @var string bankcard's type (e.g. visa, mastercard, maestro)
     */
    private $type;

    /**
     * @var int bankcard's expiration month, with or without leading zero
     */
    private $expirationMonth;

    /**
     * @var int bankcard's expiration year (4 digits)
     */
    private $expirationYear;

    /**
     * @param string $lastFour
     * @param string $mask
     * @param string $type
     * @param int $expirationMonth
     * @param int $expirationYear
     */
    public function __construct(string $lastFour, string $mask, string $type, int $expirationMonth, int $expirationYear)
    {
        parent::__construct();

        $this->lastFour = $lastFour;
        $this->mask = $mask;
        $this->type = $type;
        $this->expirationMonth = $expirationMonth;
        $this->expirationYear = $expirationYear;
    }

    /**
     * @return string
     */
    public function getLastFour(): string
    {
        return $this->lastFour;
    }

    /**
     * @return string
     */
    public function getMask(): string
    {
        return $this->mask;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
}