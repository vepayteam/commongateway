<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class CardProcessRequest extends BaseObject
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
     * @var int gate user id
     */
    private $user;

    /**
     * @var string temporary bankcard token
     */
    private $cardToken;

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
    private $acsReturnUrl;

    /**
     * @var int|null indicates whether a user wants to subscribe to recurring payments
     */
    private $recurring;

    /**
     * @var int|null days between recurring payments. When set to 0, only manual recurring remains available. Required if recurring is set to 1.
     */
    private $recurringInterval;

    /**
     * @var int|null use to check the card for the possibility of recurring payments. If set to 1, then transaction price will be put on hold and then instantly returned. Required if recurring is set to 1.
     */
    private $verifyCard;

    /**
     * @param string|null $orderId
     * @param string|null $description
     * @param int $user
     * @param string $cardToken
     * @param float $price
     * @param string $currency
     * @param string|null $acsReturnUrl
     * @param int|null $recurring
     * @param int|null $recurringInterval
     * @param int|null $verifyCard
     */
    public function __construct(
        ?string $orderId,
        ?string $description,
        int $user,
        string $cardToken,
        float $price,
        string $currency,
        ?string $acsReturnUrl,
        ?int $recurring,
        ?int $recurringInterval,
        ?int $verifyCard
    )
    {
        parent::__construct();

        $this->orderId = $orderId;
        $this->description = $description;
        $this->user = $user;
        $this->cardToken = $cardToken;
        $this->price = $price;
        $this->currency = $currency;
        $this->acsReturnUrl = $acsReturnUrl;
        $this->recurring = $recurring;
        $this->recurringInterval = $recurringInterval;
        $this->verifyCard = $verifyCard;
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
        return $this->cardToken;
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
        return $this->acsReturnUrl;
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
        return $this->recurringInterval;
    }

    /**
     * @return int|null
     */
    public function getVerifyCard(): ?int
    {
        return $this->verifyCard;
    }
}