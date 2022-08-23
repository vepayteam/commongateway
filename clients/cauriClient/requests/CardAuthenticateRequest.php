<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class CardAuthenticateRequest extends BaseObject
{
    /**
     * @var string payer authentication response. Returned from bank ACS to acs return url.
     */
    private $paRes;

    /**
     * @var string merchant data. Parameter that must be passed to bank ACS and returned from there.
     */
    private $mD;

    /**
     * @param string $paRes
     * @param string $mD
     */
    public function __construct(string $paRes, string $mD)
    {
        parent::__construct();

        $this->paRes = $paRes;
        $this->mD = $mD;
    }

    /**
     * @return string
     */
    public function getPaRes(): string
    {
        return $this->paRes;
    }

    /**
     * @return string
     */
    public function getMD(): string
    {
        return $this->mD;
    }
}