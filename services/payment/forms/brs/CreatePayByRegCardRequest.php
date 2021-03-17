<?php


namespace app\services\payment\forms\brs;


class CreatePayByRegCardRequest extends CreatePayRequest
{
    public $command = 'u';
    public $biller_client_id;
    public $perspayee_expiry;
    public $perspayee_gen = 1;
}
