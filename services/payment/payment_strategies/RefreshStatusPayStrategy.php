<?php


namespace app\services\payment\payment_strategies;


use app\models\antifraud\AntiFraud;
use app\models\bank\BankCheck;
use app\models\payonline\Cards;
use app\models\payonline\Uslugatovar;
use app\models\queue\DraftPrintJob;
use app\models\queue\ReverspayJob;
use app\models\TU;
use app\services\balance\BalanceService;
use app\services\notifications\NotificationsService;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CheckStatusPayResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\OkPayForm;
use app\services\payment\forms\SetPayOkForm;
use app\services\payment\models\PayCard;
use app\services\payment\models\PaySchet;
use app\services\payment\PaymentService;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\Json;
use yii\mutex\FileMutex;

class RefreshStatusPayStrategy extends OkPayStrategy
{
    /**
     * @return PaySchet
     * @throws Exception
     * @throws \app\services\payment\exceptions\GateException
     */
    public function exec()
    {
        $paySchet = $this->okPayForm->getPaySchet();

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($paySchet->partner, $paySchet->uslugatovar);

        /** @var CheckStatusPayResponse $checkStatusPayResponse */
        $checkStatusPayResponse = $bankAdapterBuilder->getBankAdapter()->checkStatusPay($this->okPayForm);
        if($paySchet->Status == $checkStatusPayResponse->status) {
            Yii::warning("RefreshStatusPayStrategy isNotChange");
            return $paySchet;
        }

        // Привязка карты
        if($this->isNeedLinkCard($paySchet, $checkStatusPayResponse)) {
            $this->linkCard($paySchet, $checkStatusPayResponse);
        }

        Yii::warning("RefreshStatusPayStrategy beforeConfirmPay: " . Json::encode($checkStatusPayResponse->getAttributes()));
        $this->confirmPay($paySchet, $checkStatusPayResponse);

        $paySchet->Status = $checkStatusPayResponse->status;
        $paySchet->ErrorInfo = $checkStatusPayResponse->message;
        $paySchet->RRN = $checkStatusPayResponse->xml['orderadditionalinfo']['rrn'] ?? '';
        $paySchet->RCCode = $checkStatusPayResponse->xml['orderadditionalinfo']['rc'] ?? '';
        $paySchet->save(false);

        if($paySchet->PostbackUrl) {
            $this->sendPostbackurlRequest();
        }
        if($paySchet->PostbackUrl_v2) {
            $this->sendPostbackurlV2Request();
        }

        return $paySchet;
    }

    private function sendPostbackurlRequest()
    {
        $paySchet = $this->okPayForm->getPaySchet();
        $data = [
            'status' => $paySchet->Status,
            'message' => $paySchet->ErrorInfo,
            'id' => $paySchet->ID,
            'amount' => $paySchet->SummPay,
            'extid' => $paySchet->Extid,
            'card_num' => $paySchet->CardNum,
            'card_holder' => $paySchet->CardHolder,
        ];
        $this->sendRequest($paySchet->PostbackUrl, $data);
    }

    private function sendPostbackurlV2Request()
    {
        $paySchet = $this->okPayForm->getPaySchet();
        $data = [
            'status' => $paySchet->Status,
            'message' => $paySchet->ErrorInfo,
            'id' => $paySchet->ID,
            'amount' => $paySchet->SummPay,
            'extid' => $paySchet->Extid,
            'card_num' => $paySchet->CardNum,
            'card_holder' => $paySchet->CardHolder,
        ];
        $this->sendRequest($paySchet->PostbackUrl_v2, $data);
    }

    // TODO: DRY
    private function sendRequest($url, $data)
    {
        // TODO: refact to service
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
    }
}
