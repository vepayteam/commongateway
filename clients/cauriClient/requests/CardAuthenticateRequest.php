<?php

namespace app\clients\cauriClient\requests;

use yii\base\BaseObject;

class CardAuthenticateRequest extends BaseObject
{
    /**
     * @var string payer authentication response. Returned from bank ACS to acs return url.
     */
    private $PaRes;

    /**
     * @var string merchant data. Parameter that must be passed to bank ACS and returned from there.
     */
    private $MD;

    /**
     * @param string $PaRes
     * @param string $MD
     */
    public function __construct(string $PaRes, string $MD)
    {
        parent::__construct();

        $this->PaRes = $PaRes;
        $this->MD = $MD;
    }

    /**
     * @return string
     */
    public function getPaRes(): string
    {
        return $this->PaRes;
    }

    /**
     * @return string
     */
    public function getMD(): string
    {
        return $this->MD;
    }
}