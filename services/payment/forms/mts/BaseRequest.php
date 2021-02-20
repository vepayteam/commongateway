<?php


namespace app\services\payment\forms\mts;


use yii\base\Model;

abstract class BaseRequest extends Model
{
    public $userName;
    public $password;
}
