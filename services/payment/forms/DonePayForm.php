<?php


namespace app\services\payment\forms;


use app\services\payment\models\PaySchet;
use yii\base\Model;

class DonePayForm extends Model
{
    /** @var PaySchet */
    private $paySchet;

    public $IdPay;
    public $md;
    public $paRes;
    public $cres;
    public $trans;

    /**
     * @var array
     */
    public $postParameters;

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
        if(!$this->paySchet) {
            if($this->IdPay) {
                $this->paySchet = PaySchet::findOne(['ID' => $this->IdPay]);
            } elseif($this->trans) {
                $this->paySchet = PaySchet::findOne(['ExtBillNumber' => $this->trans]);
            }
        }
        return $this->paySchet;
    }


}
