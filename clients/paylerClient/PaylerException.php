<?php

namespace app\clients\paylerClient;

use app\clients\paylerClient\responses\ErrorResponse;

class PaylerException extends \Exception
{
    /**
     * @var ErrorResponse|null
     */
    private $errorResponse;

    public function __construct(?ErrorResponse $errorResponse, \Throwable $previous)
    {
        parent::__construct('', 0, $previous);

        $this->errorResponse = $errorResponse;
    }

    /**
     * @return ErrorResponse|null
     */
    public function getErrorResponse(): ?ErrorResponse
    {
        return $this->errorResponse;
    }
}