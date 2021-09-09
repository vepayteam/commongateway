<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class GetStatementRequest extends Model
{
    public $accountNumber;
    public $startDate;
    public $endDate;

    public function rules()
    {
        return [
            [['accountNumber', 'startDate', 'endDate'], 'required'],
        ];
    }

}
