<?php


namespace app\services\payment\forms;


use app\services\payment\models\PaySchet;
use yii\base\Model;

class DonePayForm extends Model
{
    public $IdPay;
    public $md;
    public $paRes;
    public $cres;

    public function rules()
    {
        return [
            ['IdPay', 'required'],
            ['IdPay', 'number'],
            [['md', 'paRes'], 'string'],
        ];
    }

    /**
     * @return bool
     */
    public function paySchetExist()
    {
        return PaySchet::find()->where(['ID' => $this->IdPay])->exists();
    }

    /**
     * @return PaySchet
     */
    public function getPaySchet()
    {
        return PaySchet::findOne(['ID' => $this->IdPay]);
    }


}
