<?php

namespace app\services\yandexPay\models;

use yii\base\Model;

class DecryptedMessage extends Model
{
    /**
     * @var string
     */
    public $gatewayMerchantId;

    /**
     * @var string
     */
    public $messageExpiration;

    /**
     * @var string
     */
    public $messageId;

    /**
     * @var string
     */
    public $paymentMethod;

    /**
     * @var array
     */
    public $paymentMethodDetails;

    /**
     * @var array
     */
    public $transactionDetails;

    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getGatewayMerchantId(): string
    {
        return $this->gatewayMerchantId;
    }

    /**
     * @return int
     */
    public function getMessageExpiration(): int
    {
        return (int)$this->messageExpiration;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @return DecryptedMessagePaymentDetails
     */
    public function getPaymentMethodDetails(): DecryptedMessagePaymentDetails
    {
        return new DecryptedMessagePaymentDetails($this->paymentMethodDetails);
    }

    /**
     * @return DecryptedMessageTransactionDetails
     */
    public function getTransactionDetails(): DecryptedMessageTransactionDetails
    {
        return new DecryptedMessageTransactionDetails($this->transactionDetails);
    }
}
