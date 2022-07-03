<?php

namespace app\clients\payloniumClient\responses;

use yii\base\BaseObject;

class TransactionStatusResponse extends BaseObject
{
    /**
     * @var int
     */
    private $paymentId;

    /**
     * @var int
     */
    private $code;

    /**
     * @var int
     */
    private $state;

    /**
     * @var int
     */
    private $final;

    /**
     * @var int|null
     */
    private $trans;

    /**
     * @var int|null
     */
    private $fee;

    /**
     * @var string|null
     */
    private $errorDescription;

    /**
     * @param int $paymentId
     * @param int $code
     * @param int $state
     * @param int $final
     * @param int|null $trans
     */
    public function __construct(
        int $paymentId,
        int $code,
        int $state,
        int $final,
        ?int $trans,
        ?int $fee,
        ?string $errorDescription
    )
    {
        parent::__construct();

        $this->paymentId = $paymentId;
        $this->code = $code;
        $this->state = $state;
        $this->final = $final;
        $this->trans = $trans;
        $this->fee = $fee;
        $this->errorDescription = $errorDescription;
    }

    /**
     * @return int
     */
    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getFinal(): int
    {
        return $this->final;
    }

    /**
     * @return int|null
     */
    public function getTrans(): ?int
    {
        return $this->trans;
    }

    /**
     * @return int|null
     */
    public function getFee(): ?int
    {
        return $this->fee;
    }

    /**
     * @return string|null
     */
    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }
}