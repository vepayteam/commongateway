<?php


namespace app\services\auth\models;


use yii\base\Model;

class RegCreateForm extends Model
{
    public $refreshToken;

    public function rules()
    {
        return [
            [['refreshToken'], 'required'],
        ];
    }

    public function asArray()
    {
        return [
            'refresh_token' => $this->refreshToken,
        ];
    }
}
