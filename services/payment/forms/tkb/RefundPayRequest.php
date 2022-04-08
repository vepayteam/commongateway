<?php

namespace app\services\payment\forms\tkb;

use yii\base\Model;

class RefundPayRequest extends Model
{
    public $ExtId;
    public $Amount;
    public $Description = 'Отмена заказа';

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['ExtId', 'required'],
            [['ExtId', 'Amount'], 'number'],
        ];
    }
}
