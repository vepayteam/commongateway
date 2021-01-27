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

        return $paySchet;
    }
}
