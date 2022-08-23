<?php

namespace app\clients\cauriClient\objects;

use yii\base\BaseObject;

class AcsParametersObject extends BaseObject
{
    /**
     * @var string payer authentication request. Parameter that must be passed to bank ACS.
     */
    private $paReq;

    /**
     * @var string merchant data. Parameter that must be passed to bank ACS and returned from there.
     */
    private $mD;

    /**
     * @var string termination url. Payer return url after authentication on bank ACS. Parameter that must be passed to bank ACS.
     */
    private $termUrl;

    /**
     * @param string $paReq
     * @param string $mD
     * @param string $termUrl
     */
    public function __construct(string $paReq, string $mD, string $termUrl)
    {
        parent::__construct();

        $this->paReq = $paReq;
        $this->mD = $mD;
        $this->termUrl = $termUrl;
    }

    /**
     * @return string
     */
    public function getPaReq(): string
    {
        return $this->paReq;
    }

    /**
     * @return string
     */
    public function getMD(): string
    {
        return $this->mD;
    }

    /**
     * @return string
     */
    public function getTermUrl(): string
    {
        return $this->termUrl;
    }
}