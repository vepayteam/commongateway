<?php


namespace app\services\payment\exceptions;


class Check3DSv2Exception extends \Exception
{
    public const NOK = 1;
    public const INCORRECT_ECI = 2;
}
