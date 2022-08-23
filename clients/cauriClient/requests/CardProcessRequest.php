<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class CardProcessRequest extends BaseObject
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
     * @var int gate user id
     */
    private $user;

    /**
     * @var string temporary bankcard token
     */
    private $card_token;

    /**
     * @var float order price
     */
    private $price;

    /**
     * @var string currency code in the ISO 4217 alfa-3 format
     */
    private $currency;

    /**
     * @var string|null termination url. Payer return url after authentication on bank ACS.
     */
    private $acs_return_url;

    /**
     * @var int|null indicates whether a user wants to subscribe to recurring payments
     */
    private $recurring;

    /**
     * @var int|null days between recurring payments. When set to 0, only manual recurring remains available. Required if recurring is set to 1.
     */
    private $recurring_interval;

    /**
     * @var int|null use to check the card for the possibility of recurring payments. If set to 1, then transaction price will be put on hold and then instantly returned. Required if recurring is set to 1.
     */
    private $verify_card;

    /**
     * @param string|null $order_id
     * @param string|null $description
     * @param int $user
     * @param string $card_token
     * @param float $price
     * @param string $currency
     * @param string|null $acs_return_url
     * @param int|null $recurring
     * @param int|null $recurring_interval
     * @param int|null $verify_card
     */
    public function __construct(
        ?string $order_id,
        ?string $description,
        int     $user,
        string  $card_token,
        float   $price,
        string  $currency,
        ?string $acs_return_url,
        ?int    $recurring,
        ?int    $recurring_interval,
        ?int    $verify_card
    )
    {
        parent::__construct();

        $this->order_id = $order_id;
        $this->description = $description;
        $this->user = $user;
        $this->card_token = $card_token;
        $this->price = $price;
        $this->currency = $currency;
        $this->acs_return_url = $acs_return_url;
        $this->recurring = $recurring;
        $this->recurring_interval = $recurring_interval;
        $this->verify_card = $verify_card;
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
     * @return int
     */
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getCardToken(): string
    {
        return $this->card_token;
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

    /**
     * @return string|null
     */
    public function getAcsReturnUrl(): ?string
    {
        return $this->acs_return_url;
    }

    /**
     * @return int|null
     */
    public function getRecurring(): ?int
    {
        return $this->recurring;
    }

    /**
     * @return int|null
     */
    public function getRecurringInterval(): ?int
    {
        return $this->recurring_interval;
    }

    /**
     * @return int|null
     */
    public function getVerifyCard(): ?int
    {
        return $this->verify_card;
    }
}