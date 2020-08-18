<?php


namespace app\models\bank\mts_soap;


use yii\base\Model;

class RegisterP2P extends Model
{
    public $currency = 643;

    public $amount;
    public $type;
    public $orderNumber;
    public $orderDescription;
    public $returnUrl;
    public $failUrl;
    public $sessionTimeoutSecs;
    public $sessionExpiredDate;
    public $clientId;
    public $transactionTypeIndicator;
    public $features;

    public function rules()
    {
        return [
            [['amount', 'orderNumber', 'transactionTypeIndicator', 'features'], 'required'],
            [['amount', 'sessionTimeoutSecs'], 'integer'],
            [
                [
                    'orderNumber',
                    'orderDescription',
                    'returnUrl',
                    'failUrl',
                    'clientId',
                    'transactionTypeIndicator',
                    'type',
                ],
                'string'
            ],
        ];
    }



}
