<?php


namespace app\services\payment\forms;


use app\services\payment\models\PaySchet;
use yii\base\Model;

class OkPayForm extends Model
{
    public $IdPay;

    /** @var PaySchet */
    protected $paySchet;

    public function roles()
    {
        return [
            ['IdPay', 'required'],
            ['IdPay', 'number'],
        ];
    }

    /**
     * @return bool
     */
    public function existPaySchet()
    {
        return PaySchet::find()->where(['ID' => $this->IdPay])->exists();
    }

    /**
     * @return PaySchet
     */
    public function getPaySchet()
    {
        if(!$this->paySchet) {
            $this->paySchet = PaySchet::findOne(['ID' => $this->IdPay]);
        }
        return $this->paySchet;
    }

}
