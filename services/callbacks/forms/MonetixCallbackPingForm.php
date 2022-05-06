<?php

namespace app\services\callbacks\forms;

use app\services\payment\models\PaySchet;
use Yii;
use yii\base\Model;

class MonetixCallbackPingForm extends Model
{
    /** @var PaySchet */
    private $paySchet;
    public $paySchetId;

    public function rules()
    {
        return [
            ['paySchetId', 'required'],
            ['paySchetId', 'validPaySchetId'],
        ];
    }

    public function validPaySchetId()
    {
        if(!$this->getPaySchet() || $this->getPaySchet()->IPAddressUser != Yii::$app->request->remoteIP) {
            $this->addError('paySchetId', 'Доступ к данным запрещен');
        }
    }

    /**
     * @return PaySchet|null
     */
    public function getPaySchet()
    {
        if(!$this->paySchet) {
            $this->paySchet = PaySchet::findOne(['ID' => $this->paySchetId]);
        }
        return $this->paySchet;
    }
}