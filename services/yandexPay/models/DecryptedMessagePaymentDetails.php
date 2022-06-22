<?php

namespace app\services\yandexPay\models;

use yii\base\Model;

class DecryptedMessagePaymentDetails extends Model
{
    /**
     * @var string
     */
    public $authMethod;

    /**
     * @var int
     */
    public $expirationMonth;

    /**
     * @var int
     */
    public $expirationYear;

    /**
     * @var string
     */
    public $pan;

    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getAuthMethod(): string
    {
        return $this->authMethod;
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
     * Возвращяет expiration карты в формате MMYY
     *
     * @return string
     */
    public function getFullExpiration(): string
    {
        $strMonth = (string)$this->getExpirationMonth();
        $strYear = (string)$this->getExpirationYear();

        $strMonth = str_pad($strMonth, 2, '0', STR_PAD_LEFT);

        return "{$strMonth}{$strYear}";
    }

    /**
     * @return string
     */
    public function getPan(): string
    {
        return $this->pan;
    }
}