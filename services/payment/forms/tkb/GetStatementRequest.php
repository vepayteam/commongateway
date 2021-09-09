<?php


namespace app\services\payment\forms\tkb;


use yii\base\Model;

class GetStatementRequest extends Model
{
    public $Account;
    public $StartDate;
    public $EndDate;

    public function rules()
    {
        return [
            [['Account', 'StartDate', 'EndDate'], 'required'],
        ];
    }

}
