<?php


namespace app\services\payment\forms\brs;


use app\services\payment\models\PartnerBankGate;
use yii\base\Model;
use yii\helpers\Json;

/**
 * Class TransferToAccountRequest
 * @package app\services\payment\forms\brs
 */
class TransferToAccountRequest extends Model
{
    use XmlRequestTrait {
        buildSignature as protected;
    }

    public $bic;
    public $currency = 'RUB';
    public $receiverId;
    public $merchantId;
    public $firstName;
    public $lastName;
    public $middleName;
    public $amount;
    public $account;
    public $receiverIdType = 'MTEL';
    public $sourceId;

    /**
     * @param PartnerBankGate $partnerBankGate
     * @return string
     */
    public function getMsgSign(PartnerBankGate $partnerBankGate)
    {
        $body = $this->buildBody();
        $sign = $this->buildSignature($body, $partnerBankGate);
        return $sign;
    }

    /**
     * @return string
     */
    private function buildBody()
    {
        $sortedKeys = [
            'sourceId',
            'merchantId',
            'account',
            'receiverId',
            'receiverIdType',
            'bic',
            'firstName',
            'lastName',
            'middleName',
            'amount',
            'currency',
        ];

        $sortedAttributes = [];
        foreach ($sortedKeys as $key) {
            $sortedAttributes[$key] = $this->$key;
        }

        return Json::encode($sortedAttributes);
    }
}
