<?php

namespace app\clients\cauriClient\responses;

use yii\base\BaseObject;

class CardAuthenticateResponse extends BaseObject
{
    /**
     * @var int identifier of a transaction
     */
    private $id;

    /**
     * @var bool indicates whether request has been accepted for processing
     */
    private $success;

    /**
     * @var string|null response code of a transaction. Required only when success is false.
     */
    private $responseCode;

    /**
     * @param int $id
     * @param bool $success
     * @param string|null $responseCode
     */
    public function __construct(int $id, bool $success, ?string $responseCode)
    {
        parent::__construct();

        $this->id = $id;
        $this->success = $success;
        $this->responseCode = $responseCode;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string|null
     */
    public function getResponseCode(): ?string
    {
        return $this->responseCode;
    }
}