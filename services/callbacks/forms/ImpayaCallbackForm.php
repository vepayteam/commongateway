<?php

namespace app\services\callbacks\forms;

use app\models\traits\ValidateFormTrait;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use yii\base\Model;

class ImpayaCallbackForm extends Model
{
    use ValidateFormTrait;

    public $merchant_id;
    public $mc_transaction_id;
    public $transaction_id;
    public $amount;
    public $currency;
    public $status_id;
    public $hash;
    public $payment_system_status;

    public function rules()
    {
        return [
            [[
                'merchant_id', 'mc_transaction_id', 'transaction_id',
                'amount', 'currency', 'status_id', 'hash', 'payment_system_status'
            ], 'required'],
            [['merchant_id', 'mc_transaction_id', 'transaction_id', 'amount', 'status_id'], 'number'],
            ['hash', 'validateHash'],
        ];
    }

    public function validateHash()
    {
        try {
            $paySchet = PaySchet::findOne(['ID' => $this->mc_transaction_id]);
            /** @var PartnerBankGate $partnerBankGate */
            $partnerBankGate = $paySchet->partner->getBankGates()
                ->where(['Login' => $this->merchant_id, 'Enable' => 1])
                ->andWhere(['not', ['Token' => null]])
                ->orderBy('Priority DESC')
                ->one();
            $hashBase = md5(
                $this->transaction_id . $this->amount . $this-> currency
                . $this->merchant_id . $this->status_id . $partnerBankGate->Token
            );
            if($hashBase != $this->hash) {
                $this->addError("hash", "Not valid hash");
            }
        } catch (\Exception $e) {
            $this->addError("hash", "Not valid hash");
        }
    }

    public function getPaySchet(): PaySchet
    {
        return PaySchet::findOne(['ID' => $this->mc_transaction_id]);
    }

}