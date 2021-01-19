<?php


namespace app\services\payment\forms;


use app\services\payment\models\PaySchet;
use yii\base\Model;

class RefundPayForm extends Model
{
    /** @var PaySchet */
    public $paySchet;

    public function rules()
    {
        return [
            ['paySchet', 'required'],
        ];
    }

}
