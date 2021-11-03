<?php

namespace app\services\payment;

use app\services\base\Structure;

/**
 * Class CurlSSLStructure
 */
class CurlSSLStructure extends Structure
{
    /**
     * @var string $sslcerttype
     */
    public $sslcerttype;
    /**
     * @var string $sslkeytype
     */
    public $sslkeytype;
    /**
     * @var string $cainfo
     */
    public $cainfo;
    /**
     * @var string $sslcert
     */
    public $sslcert;
    /**
     * @var string $sslkey
     */
    public $sslkey;
}
