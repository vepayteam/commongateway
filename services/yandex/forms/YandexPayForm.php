<?php

namespace app\services\yandex\forms;

use yii\base\Model;

class YandexPayForm extends Model
{
    public $paymentToken;

    public function rules()
    {
        return [
            [['paymentToken'], 'required'],
            [['paymentToken'], 'string'],
        ];
    }
}
