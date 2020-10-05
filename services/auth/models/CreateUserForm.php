<?php


namespace app\services\auth\models;


use yii\base\Model;

class CreateUserForm extends Model
{

    public function rules()
    {
        return [
            [['login', 'email', 'merchant_name'], 'required'],
        ];
    }

}
