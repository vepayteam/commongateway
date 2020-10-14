<?php


namespace app\services\payment\models\adg;


use yii\base\Model;

class TxDetailsModel extends Model
{
    public $apiVersion = '1.0.1';
    public $requestId;
    public $recurrentType;
    public $perform3DS = '1';
    /** @var OrderDataModel */
    public $orderData;
    public $cancelUrl;
    public $returnUrl;

    public function rules()
    {
        return [
            [['apiVersion', 'requestId', 'orderData', 'cancelUrl', 'returnUrl'], 'required'],
            ['orderData', 'validateOrderData'],
        ];
    }

    public function validateOrderData()
    {
        if(!$this->orderData->validate()) {
            $this->addError('orderData', 'validateOrderData error');
        }
    }

    public function getAttributes($names = null, $except = [])
    {
        $result = parent::getAttributes($names, $except);
        $result['orderData'] = $this->orderData->getAttributes();
        return $result;
    }

    public function getSignature()
    {
        return trim($this->apiVersion)
        . trim($this->requestId)
        . trim((string)$this->recurrentType)
        . trim((string)$this->perform3DS)
        . $this->orderData->getSignature()
        . trim($this->cancelUrl)
        . trim($this->returnUrl);
    }

}
