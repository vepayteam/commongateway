<?php

namespace app\clients\yandexPayClient\responses;

use yii\base\BaseObject;

class PaymentUpdateResponse extends BaseObject
{
    /**
     * @var string
     */
    private $status;

    /**
     * @param string $status
     */
    public function __construct(string $status)
    {
        parent::__construct();

        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
