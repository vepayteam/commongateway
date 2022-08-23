<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class CardProcessRecurringRequest extends BaseObject
{
    /**
     * @var string|null merchant's order id of a transaction
     */
    private $orderId;

    /**
     * @var string|null order description
     */
    private $description;

    /**
     * @var string recurring profile token
     */
    private $recurringProfile;

    /**
     * @var float order price
     */
    private $price;

    /**
     * @var string currency code in the ISO 4217 alfa-3 format
     */
    private $currency;

    /**
     * @param string|null $orderId
     * @param string|null $description
     * @param string $recurringProfile
     * @param float $price
     * @param string $currency
     */
    public function __construct(?string $orderId, ?string $description, string $recurringProfile, float $price, string $currency)
    {
        parent::__construct();

        $this->orderId = $orderId;
        $this->description = $description;
        $this->recurringProfile = $recurringProfile;
        $this->price = $price;
        $this->currency = $currency;
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getRecurringProfile(): string
    {
        return $this->recurringProfile;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }
}