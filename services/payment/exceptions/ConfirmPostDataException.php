<?php

namespace app\services\payment\exceptions;

/**
 * Error received in POST-parameter when client returned from ACS (3DS verification).
 */
class ConfirmPostDataException extends \Exception
{
    /**
     * @var string
     */
    private $bankErrorMessage;

    /**
     * @param string $errorMessage
     */
    public function __construct(string $errorMessage = '')
    {
        parent::__construct("Error received from bank on return from ACS: \"{$errorMessage}\".");

        $this->bankErrorMessage = $errorMessage;
    }

    /**
     * @return string
     */
    public function getBankErrorMessage(): string
    {
        return $this->bankErrorMessage;
    }
}