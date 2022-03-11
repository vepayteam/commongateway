<?php

namespace app\services\payment\forms;

use yii\base\Model;

class ClientForm extends Model
{
    public $email;
    public $phone;
    public $zip;
    public $login;
    public $firstName;
    public $lastName;
    public $country;
    public $browserData = [];

}