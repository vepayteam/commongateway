<?php

namespace app\clients\tcbClient\responses\objects;

use yii\base\BaseObject;

/**
 * @property-read string|null $cardExpYear
 * @property-read string|null $cardExpMonth
 * @property-read string|null $cardIssuingBank
 * @property-read string|null $cardBrand Payment system. MASTERCARD/VISA/MIR etc.
 * @property-read string|null $cardType debit/credit.
 * @property-read string|null $cardLevel standart/platinum etc.
 * @property-read string|null $lastStateDate
 * @property-read string|null $cardNumber Masked card number, eg. "8888 88** **** 8888"
 * @property-read string|null $cardHolder
 * @property-read string|null $cardRefId Card UID in TCB.
 * @property-read string|null $actionCodeDescription
 * @property-read string|null $eci
 * @property-read string|null $cardNumberHash
 * @property-read string|null $rc
 * @property-read string|null $fee
 * @property-read string|null $rrn
 */
class OrderAdditionalInfo extends BaseObject
{
    private $_cardExpYear;
    private $_cardExpMonth;
    private $_cardIssuingBank;
    private $_cardBrand;
    private $_cardType;
    private $_cardLevel;
    private $_lastStateDate;
    private $_cardNumber;
    private $_cardHolder;
    private $_cardRefId;
    private $_actionCodeDescription;
    private $_eci;
    private $_cardNumberHash;
    private $_rc;
    private $_fee;
    private $_rrn;

    public function __construct(
        ?string $cardExpYear,
        ?string $cardExpMonth,
        ?string $cardIssuingBank,
        ?string $cardBrand,
        ?string $cardType,
        ?string $cardLevel,
        ?string $lastStateDate,
        ?string $cardNumber,
        ?string $cardHolder,
        ?string $cardRefId,
        ?string $actionCodeDescription,
        ?string $eci,
        ?string $cardNumberHash,
        ?string $rc,
        ?string $fee,
        ?string $rrn
    )
    {
        parent::__construct();

        $this->_cardExpYear = $cardExpYear;
        $this->_cardExpMonth = $cardExpMonth;
        $this->_cardIssuingBank = $cardIssuingBank;
        $this->_cardBrand = $cardBrand;
        $this->_cardType = $cardType;
        $this->_cardLevel = $cardLevel;
        $this->_lastStateDate = $lastStateDate;
        $this->_cardNumber = $cardNumber;
        $this->_cardHolder = $cardHolder;
        $this->_cardRefId = $cardRefId;
        $this->_actionCodeDescription = $actionCodeDescription;
        $this->_eci = $eci;
        $this->_cardNumberHash = $cardNumberHash;
        $this->_rc = $rc;
        $this->_fee = $fee;
        $this->_rrn = $rrn;
    }

    /**
     * @return string|null
     */
    public function getCardExpYear(): ?string
    {
        return $this->_cardExpYear;
    }

    /**
     * @return string|null
     */
    public function getCardExpMonth(): ?string
    {
        return $this->_cardExpMonth;
    }

    /**
     * @return string|null
     */
    public function getCardIssuingBank(): ?string
    {
        return $this->_cardIssuingBank;
    }

    /**
     * @return string|null
     */
    public function getCardBrand(): ?string
    {
        return $this->_cardBrand;
    }

    /**
     * @return string|null
     */
    public function getCardType(): ?string
    {
        return $this->_cardType;
    }

    /**
     * @return string|null
     */
    public function getCardLevel(): ?string
    {
        return $this->_cardLevel;
    }

    /**
     * @return string|null
     */
    public function getLastStateDate(): ?string
    {
        return $this->_lastStateDate;
    }

    /**
     * @return string|null
     */
    public function getCardNumber(): ?string
    {
        return $this->_cardNumber;
    }

    /**
     * @return string|null
     */
    public function getCardHolder(): ?string
    {
        return $this->_cardHolder;
    }

    /**
     * @return string|null
     */
    public function getCardRefId(): ?string
    {
        return $this->_cardRefId;
    }

    /**
     * @return string|null
     */
    public function getActionCodeDescription(): ?string
    {
        return $this->_actionCodeDescription;
    }

    /**
     * @return string|null
     */
    public function getEci(): ?string
    {
        return $this->_eci;
    }

    /**
     * @return string|null
     */
    public function getCardNumberHash(): ?string
    {
        return $this->_cardNumberHash;
    }

    /**
     * @return string|null
     */
    public function getRc(): ?string
    {
        return $this->_rc;
    }

    /**
     * @return string|null
     */
    public function getFee(): ?string
    {
        return $this->_fee;
    }

    /**
     * @return string|null
     */
    public function getRrn(): ?string
    {
        return $this->_rrn;
    }
}