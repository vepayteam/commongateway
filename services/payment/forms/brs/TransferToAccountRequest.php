<?php


namespace app\services\payment\forms\brs;


use app\services\payment\banks\BRSAdapter;
use app\services\payment\models\PartnerBankGate;
use Yii;
use yii\base\Model;
use yii\helpers\Json;

/**
 * Class TransferToAccountRequest
 * @package app\services\payment\forms\brs
 */
class TransferToAccountRequest extends Model
{
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
        $bodyUtf8 = iconv(mb_detect_encoding($body), 'UTF-8', $body);
        $sign = $this->buildSignature($bodyUtf8, $partnerBankGate);
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

    /**
     * @param string $body
     * @return string
     */
    protected function buildSignature(string $body, PartnerBankGate $partnerBankGate)
    {
        if(!file_exists(Yii::getAlias('@runtime/requests'))) {
            mkdir(Yii::getAlias('@runtime/requests'), 0777);
        }

        $fileRequest = Yii::getAlias('@runtime/requests/' . Yii::$app->security->generateRandomString(32) . '.txt');
        $fileResponse = Yii::getAlias('@runtime/requests/' . Yii::$app->security->generateRandomString(32) . '.txt');
        file_put_contents($fileRequest, $body);

        $cmd  = sprintf('openssl dgst -sha256 -sign "%s" "%s" > "%s"',
            Yii::getAlias(BRSAdapter::KEYS_PATH . $partnerBankGate->Login . '.key'),
            $fileRequest,
            $fileResponse
        );
        shell_exec($cmd);

        $signature = file_get_contents($fileResponse);
        // $signature = explode('=', $signature)[1];
        $signature = trim($signature);

        unlink($fileRequest);
        unlink($fileResponse);
        return base64_encode($signature);
    }
}