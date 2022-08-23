<?php

namespace app\clients\cauriClient\objects;

use yii\base\BaseObject;

class AcsParametersObject extends BaseObject
{
    /**
     * @var string payer authentication request. Parameter that must be passed to bank ACS.
     */
    private $PaReq;

    /**
     * @var string merchant data. Parameter that must be passed to bank ACS and returned from there.
     */
    private $MD;

    /**
     * @var string termination url. Payer return url after authentication on bank ACS. Parameter that must be passed to bank ACS.
     */
    private $TermUrl;

    /**
     * @param string $PaReq
     * @param string $MD
     * @param string $TermUrl
     */
    public function __construct(string $PaReq, string $MD, string $TermUrl)
    {
        parent::__construct();

        $this->PaReq = $PaReq;
        $this->MD = $MD;
        $this->TermUrl = $TermUrl;
    }

    /**
     * @return string
     */
    public function getPaReq(): string
    {
        return $this->PaReq;
    }

    /**
     * @return string
     */
    public function getMD(): string
    {
        return $this->MD;
    }

    /**
     * @return string
     */
    public function getTermUrl(): string
    {
        return $this->TermUrl;
    }
}