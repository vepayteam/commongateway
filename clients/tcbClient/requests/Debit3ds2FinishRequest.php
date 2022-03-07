<?php

namespace app\clients\tcbClient\requests;

use app\clients\tcbClient\requests\objects\AuthenticationData;
use yii\base\BaseObject;

/**
 * Request for the DebitUnregisteredCard3ds2WofFinish method.
 *
 * @property-read string $extId
 * @property-read string $cardRefId
 * @property-read string $amount
 * @property-read string $forceGate
 * @property-read AuthenticationData $authenticationData
 * @property-read string $description
 */
class Debit3ds2FinishRequest extends BaseObject
{
    public const FORCE_GATE_ECOM = 'ECOM';
    public const FORCE_GATE_AFT = 'AFT';

    private $_extId;
    private $_cardRefId;
    private $_amount;
    private $_forceGate;
    private $_authenticationData;
    private $_description;

    public static function forceGateList(): array
    {
        return [
            static::FORCE_GATE_AFT,
            static::FORCE_GATE_ECOM,
        ];
    }

    public function __construct(
        string $extId,
        string $cardRefId,
        string $amount,
        string $forceGate,
        AuthenticationData $authenticationData,
        string $description
    )
    {
        parent::__construct();

        if (!in_array($forceGate, static::forceGateList())) {
            \Yii::warning("Debit3ds2FinishRequest unknown ForceGate value: {$forceGate}.");
        }

        $this->_extId = $extId;
        $this->_cardRefId = $cardRefId;
        $this->_amount = $amount;
        $this->_forceGate = $forceGate;
        $this->_authenticationData = $authenticationData;
        $this->_description = $description;
    }

    public function getExtId(): string
    {
        return $this->_extId;
    }

    public function getCardRefId(): string
    {
        return $this->_cardRefId;
    }

    public function getAmount(): string
    {
        return $this->_amount;
    }

    public function getForceGate(): string
    {
        return $this->_forceGate;
    }

    public function getAuthenticationData(): AuthenticationData
    {
        return $this->_authenticationData;
    }

    public function getDescription(): string
    {
        return $this->_description;
    }
}