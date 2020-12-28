<?php


namespace app\modules\partner\models;


use app\models\traits\ValidateFormTrait;
use app\services\payment\models\PaySchet;
use yii\base\Model;

/**
 * Class PaySchetLogForm
 * @package app\modules\partner\models
 *
 * @property PaySchet $paySchet
 */
class PaySchetLogForm extends Model
{
    use ValidateFormTrait;

    /** @var int */
    public $paySchetId;


    public function rules()
    {
        return [
            ['paySchetId', 'required'],
            ['paySchetId', 'number'],
            ['paySchetId', 'validatePaySchet'],
        ];
    }

    public function validatePaySchet()
    {
        if($this->paySchet === null) {
            $this->addError('paySchetId', 'Счет не существует');
        }
    }

    /**
     * @return PaySchet|null
     */
    public function getPaySchet()
    {
        return PaySchet::findOne(['ID' => $this->paySchetId]);
    }
}
