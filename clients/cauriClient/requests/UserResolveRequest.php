<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class UserResolveRequest extends BaseObject
{
    /**
     * @var string unique and unchangeable merchant's identifier of a user
     */
    private $identifier;

    /**
     * @var string|null full name of nickname of a user
     */
    private $display_name;

    /**
     * @var string email address of a user
     */
    private $email;

    /**
     * @var string|null phone number of a user in international format, without the leading + and any separators
     */
    private $phone;

    /**
     * @var string|null locale of a user in ISO 639-1 format. Default is merchant’s locale.
     */
    private $locale;

    /**
     * @var string|null IP address of a user
     */
    private $ip;

    /**
     * @param string $identifier
     * @param string|null $display_name
     * @param string $email
     * @param string|null $phone
     * @param string|null $locale
     * @param string|null $ip
     */
    public function __construct(
        string  $identifier,
        ?string $display_name,
        string  $email,
        ?string $phone,
        ?string $locale,
        ?string $ip
    )
    {
        parent::__construct();

        $this->identifier = $identifier;
        $this->display_name = $display_name;
        $this->email = $email;
        $this->phone = $phone;
        $this->locale = $locale;
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->display_name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }
}