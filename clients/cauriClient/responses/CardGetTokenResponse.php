<?php

namespace app\clients\cauriClient\responses;

use yii\base\BaseObject;

class CardGetTokenResponse extends BaseObject
{
    /**
     * @var string Token ID
     */
    private $id;

    /**
     * @var string The date and time when the token expires in the ISO 8601 format
     */
    private $expiresAt;

    /**
     * @param string $id
     * @param string $expiresAt
     */
    public function __construct(string $id, string $expiresAt)
    {
        parent::__construct();

        $this->id = $id;
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }
}