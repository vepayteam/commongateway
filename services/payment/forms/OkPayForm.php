<?php


namespace app\services\payment\forms;


use app\services\payment\models\PaySchet;
use yii\base\Model;

/**
 * @property-read PaySchet $paySchet
 */
class OkPayForm extends Model
{
    public $IdPay;

    /** @var PaySchet */
    private $_paySchet;

    public function rules()
    {
        return [
            ['IdPay', 'required'],
            ['IdPay', 'number'],
        ];
    }

    /**
     * @return PaySchet|null
     */
    public function getPaySchet(): ?PaySchet
    {
        if ($this->_paySchet === null) {
            $this->_paySchet = PaySchet::findOne(['ID' => $this->IdPay]);
        }
        return $this->_paySchet;
    }
}
