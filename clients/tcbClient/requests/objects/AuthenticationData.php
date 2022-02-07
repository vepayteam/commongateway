<?php

namespace app\clients\tcbClient\requests\objects;

use yii\base\BaseObject;

/**
 * Authentication data for 3DS.
 *
 * @property-read string $status
 * @property-read string|null $authenticationValue CAVV/AAV/NSPK-CAV.
 * @property-read string|null $dsTransId Transaction ID returned by payment system Directory Server.
 * @property-read string|null $eci Electronic Commerce Indicator.
 */
class AuthenticationData extends BaseObject
{
    /** Successful. */
    public const STATUS_OK = 'OK';
    /** Not successful. */
    public const STATUS_NOK = 'NOK';
    /** Not involved in 3DS. */
    public const STATUS_ACQ = 'ACQ';

    private $_status;
    private $_authenticationValue;
    private $_dsTransId;
    private $_eci;

    public static function statusList(): array
    {
        return [
            static::STATUS_OK,
            static::STATUS_NOK,
            static::STATUS_ACQ,
        ];
    }

    public function __construct(
        string $status,
        ?string $authenticationValue = null,
        ?string $dsTransID = null,
        ?string $eci = null
    )
    {
        parent::__construct();

        if (!in_array($status, static::statusList())) {
            \Yii::warning("AuthenticationData unknown status: {$status}.");
        }

        $this->_status = $status;
        $this->_authenticationValue = $authenticationValue;
        $this->_dsTransId = $dsTransID;
        $this->_eci = $eci;
    }

    public function getStatus(): string
    {
        return $this->_status;
    }

    public function getAuthenticationValue(): ?string
    {
        return $this->_authenticationValue;
    }

    public function getDsTransId(): ?string
    {
        return $this->_dsTransId;
    }

    public function getEci(): ?string
    {
        return $this->_eci;
    }
}