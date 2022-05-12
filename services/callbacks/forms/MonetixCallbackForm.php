<?php

namespace app\services\callbacks\forms;

use app\services\payment\banks\MonetixAdapter;
use app\services\payment\models\PaySchet;
use Yii;
use yii\base\Model;
use yii\helpers\Json;

class MonetixCallbackForm extends Model
{
    /** @var PaySchet */
    private $paySchet;

    public $paySchetId;
    public $transId;
    public $status;
    public $message;
    public $data;

    public function rules()
    {
        return [
            [['paySchetId', 'transId', 'status', 'data'], 'required'],
            [['message', 'paySchetId', 'transId', 'status'], 'string'],
            ['paySchetId', 'validatePaySchetId'],
        ];
    }

    public function validatePaySchetId()
    {
        $paySchet = $this->getPaySchet();
        if(!$paySchet || $paySchet->ExtBillNumber != $this->transId || $paySchet->Bank != MonetixAdapter::$bank) {
            Yii::warning('MonetixCallbackForm validatePaySchetId error: ' . Json::encode($this->getAttributes()));
            $this->addError('paySchetId', 'Ошибка проверки номера транзакции');
        }
    }

    public function getPaySchet()
    {
        if(!$this->paySchet) {
            $this->paySchet = PaySchet::findOne(['ID' => $this->paySchetId]);
        }
        return $this->paySchet;
    }

}