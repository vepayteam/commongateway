<?php


namespace app\services\auth\models;


use yii\base\Model;

class RefreshForm extends Model implements IClientForm
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
