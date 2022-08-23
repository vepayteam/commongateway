<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class CardProcessRecurringRequest extends BaseObject
{
    /**
     * @var string|null merchant's order id of a transaction
     */
    private $order_id;

    /**
     * @var string|null order description
     */
    private $description;

    /**
     * @var string recurring profile token
     */
    private $recurring_profile;

    /**
     * @var float order price
     */
    private $price;

    /**
     * @var string currency code in the ISO 4217 alfa-3 format
     */
    private $currency;

    /**
     * @param string|null $order_id
     * @param string|null $description
     * @param string $recurring_profile
     * @param float $price
     * @param string $currency
     */
    public function __construct(?string $order_id, ?string $description, string $recurring_profile, float $price, string $currency)
    {
        parent::__construct();

        $this->order_id = $order_id;
        $this->description = $description;
        $this->recurring_profile = $recurring_profile;
        $this->price = $price;
        $this->currency = $currency;
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        return $this->order_id;
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
        return $this->recurring_profile;
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