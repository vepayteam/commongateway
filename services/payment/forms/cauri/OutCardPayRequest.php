<?php


namespace app\services\payment\forms\cauri;


use yii\base\Model;

class OutCardPayRequest extends Model
{
    public $type = 0;
    public $amount;
    public $currency = 'RUB';
    public $description;
    public $orderId;
    public $account;
    public $beneficiaryFirstName;
    public $beneficiaryLastName;

    public $birthDate;
    public $countryOfCitizenship;
    public $countryOfResidence;
    public $documentType;
    public $documentIssuer;
    public $documentIssuedAt;
    public $documentValidUntil;
    public $birthPlace;
    public $documentSeries;
    public $documentNumber;
    public $phone;


}
