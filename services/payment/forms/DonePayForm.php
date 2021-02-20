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
    public $trans;

    public function rules()
    {
        return [
            ['IdPay', 'validateIdPay'],
            ['IdPay', 'number'],
            [['md', 'paRes'], 'string'],
        ];
    }

    public function validateIdPay()
    {
        if(empty($this->IdPay) && empty($this->trans)) {
            $this->addError('IdPay', 'Ид счета или транзацкции обязательны');
        }
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
