<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class IdentRequest extends Model
{
    public $ExtId;
    public $FirstName;
    public $LastName;
    public $Patronymic;
    public $Series;
    public $Number;

    public $BirthDay;
    public $Inn;
    public $Snils;
    public $IssueData;
    public $IssueCode;
    public $Issuer;
    public $PhoneNumber;
}
