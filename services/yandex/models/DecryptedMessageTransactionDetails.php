<?php

namespace app\services\yandex\models;

use yii\base\Model;

class DecryptedMessageTransactionDetails extends Model
{
    /**
     * @var int
     */
    public $amount;

    /**
     * @var string
     */
    public $currency;

    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }
}