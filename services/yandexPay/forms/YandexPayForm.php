<?php

namespace app\services\yandexPay\forms;

use yii\base\Model;

class YandexPayForm extends Model
{
    public $paymentToken;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['paymentToken'], 'required'],
            [['paymentToken'], 'string'],
        ];
    }
}
