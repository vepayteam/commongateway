<?php


namespace app\services\payment\forms\adg;


use app\services\payment\forms\CreatePayForm;
use app\services\payment\helpers\ADGroupBankHelper;
use app\services\payment\models\adg\ClientCardModel;
use app\services\payment\models\adg\OrderDataModel;
use app\services\payment\models\adg\TxDetailsModel;
use yii\base\Model;

class CreatePayRequest extends Model
{
    public $mId;
    public $maId;
    public $userName;
    public $password;
    public $lang = 'ru';
    public $metaData;
    /** @var TxDetailsModel */
    public $txDetails;

    public $signature;

    public function rules()
    {
        return [
            [['mId', 'maId', 'userName', 'password', 'lang', 'metaData', 'txDetails'], 'required'],
            ['txDetails', 'validateTxDetails'],
            ['metaData', 'validateMetaData'],
        ];
    }

    public function validateTxDetails()
    {
        if(!$this->txDetails->validate()) {
            $this->addError('txDetails', 'validateTxDetails error');
        }
    }

    public function validateMetaData()
    {
        if(!is_array($this->metaData) || !array_key_exists('merchantUserId', $this->metaData)) {
            $this->addError('metaData', 'validateMetaData error');
        }
    }

    public function getAttributes($names = null, $except = [])
    {
        $result = parent::getAttributes($names, $except);
        $result['txDetails'] = $this->txDetails->getAttributes();
        return $result;
    }

    public function buildData(CreatePayForm $createPayForm)
    {
        $this->metaData = [
            '' => \Yii::$app->security->generateRandomString(),
        ];

        $cc = new ClientCardModel();
        $cc->ccNumber = $createPayForm->CardNumber;
        $cc->cardHolderName = $createPayForm->CardHolder;
        $cc->cvv = $createPayForm->CardCVC;
        $cc->expirationMonth = $createPayForm->CardMonth;
        $cc->expirationYear = '20' . $createPayForm->CardYear;

        $orderDataModel = new OrderDataModel();
        $orderDataModel->orderId = $createPayForm->getPaySchet()->ID;
        $orderDataModel->orderDescription = 'Платеж №' . $createPayForm->getPaySchet()->ID;
        $orderDataModel->amount = round($createPayForm->getPaySchet()->getSummFull() / 100, 2);
        $orderDataModel->cc = $cc;

        $txDetails = new TxDetailsModel();
        $txDetails->requestId = $createPayForm->getPaySchet()->ID;
        $txDetails->recurrentType = '1';
        $txDetails->orderData = $orderDataModel;
        $txDetails->cancelUrl = \Yii::$app->params['domain'] . '/pay/orderdone?id=' . $createPayForm->getPaySchet()->ID;
        $txDetails->returnUrl = \Yii::$app->params['domain'] . '/pay/orderdone?id=' . $createPayForm->getPaySchet()->ID;

        $this->txDetails = $txDetails;
    }

    public function getSignature()
    {
        return trim($this->mId)
            . trim($this->maId)
            . trim($this->userName)
            . trim($this->password)
            . trim($this->metaData['merchantUserId'])
            . $this->txDetails->getSignature();
    }



}
