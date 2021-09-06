<?php

namespace app\services\payment\forms\decta\payout;

use app\services\base\Structure;

/**
 * Class Client
 *
 * @package app\services\payment\forms\decta\payout
 */
class Client extends Structure
{
    /**
     * @var string $email
     */
    public $email;
    /**
     * @var string $phone
     */
    public $phone;
    /**
     * @var string $first_name
     */
    public $first_name;
    /**
     * @var string $last_name
     */
    public $last_name;
    /**
     * @var string $birth_date
     */
    public $birth_date;
    /**
     * @var string $personal_code
     */
    public $personal_code;
    /**
     * @var string $brand_name
     */
    public $brand_name;
    /**
     * @var string $legal_name
     */
    public $legal_name;
    /**
     * @var string $registration_nr
     */
    public $registration_nr;
    /**
     * @var string $vat_payer_nr
     */
    public $vat_payer_nr;
    /**
     * @var string $legal_address
     */
    public $legal_address;
    /**
     * @var array $country
     */
    public $country;
    /**
     * @var string $state
     */
    public $state;
    /**
     * @var string $city
     */
    public $city;
    /**
     * @var string $zip_code
     */
    public $zip_code;
    /**
     * @var string $bank_account
     */
    public $bank_account;
    /**
     * @var string $bank_code
     */
    public $bank_code;
    /**
     * @var array $cc
     */
    public $cc;
    /**
     * @var array $bcc
     */
    public $bcc;
    /**
     * @var string $original_client
     */
    public $original_client;
    /**
     * @var string $recurring_card
     */
    public $recurring_card;
}
