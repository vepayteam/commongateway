<?php


namespace app\services\payment\payment_strategies;


use app\clients\tcbClient\TcbOrderNotExistException;
use app\models\antifraud\AntiFraud;
use app\models\bank\BankCheck;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
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
     * @throws TcbOrderNotExistException
     */
    public function exec()
    {
        $paySchet = $this->okPayForm->getPaySchet();
        $bankAdapterBuilder = new BankAdapterBuilder();

        // TODO: refact
        $partner = $paySchet->partner;
        if($paySchet->partner->ID == 1
            && in_array($paySchet->uslugatovar->IsCustom, [
                TU::$VYPLATVOZN,
                TU::$REVERSCOMIS,
                TU::$PEREVPAYS,
                TU::$VYVODPAYS]
            )
            && !empty($paySchet->uslugatovar->ExtReestrIDUsluga)
        ) {
            Yii::warning("RefreshStatusPayStrategy isVyvod ID=" . $this->okPayForm->IdPay);
            $partner = Partner::findOne(['ID' => $paySchet->uslugatovar->ExtReestrIDUsluga]);
        }

        Yii::warning(sprintf(
            "RefreshStatusPayStrategy beforeBuild ID=%d,  partnerId=%d, uslugaId=%d, bankId=%d",
            $this->okPayForm->IdPay,
            $partner->ID,
            $paySchet->uslugatovar->ID,
            $paySchet->bank->ID)
        );

        $bankAdapterBuilder->buildByBank($partner, $paySchet->uslugatovar, $paySchet->bank, $paySchet->currency);

        /** @var CheckStatusPayResponse $checkStatusPayResponse */
        $checkStatusPayResponse = $bankAdapterBuilder->getBankAdapter()->checkStatusPay($this->okPayForm);
        if($paySchet->Status == $checkStatusPayResponse->status && $paySchet->ErrorInfo == $checkStatusPayResponse->message) {
            Yii::warning("RefreshStatusPayStrategy isNotChange");
            return $paySchet;
        }

        // Привязка карты
        if($this->isNeedLinkCard($paySchet, $checkStatusPayResponse)) {
            $this->linkCard($paySchet, $checkStatusPayResponse);
        }

        Yii::warning("RefreshStatusPayStrategy beforeConfirmPay: " . Json::encode($checkStatusPayResponse->getAttributes()));
        $this->confirmPay($paySchet, $checkStatusPayResponse);

        if ($paySchet->isRefund && $checkStatusPayResponse->status === BaseResponse::STATUS_DONE) {
            $paySchet->Status = PaySchet::STATUS_REFUND_DONE;
        } else {
            $paySchet->Status = $checkStatusPayResponse->status;
        }
        $paySchet->ErrorInfo = $checkStatusPayResponse->message;
        $paySchet->RRN = $checkStatusPayResponse->rrn;
        $paySchet->Operations = Json::encode($checkStatusPayResponse->operations ?? []);

        // xml['orderadditionalinfo']['rc'] это ТКБшный result code TODO может перенести в $checkStatusPayResponse->rcCode?
        if (isset($checkStatusPayResponse->xml['orderadditionalinfo']['rc'])) {
            $paySchet->RCCode = $checkStatusPayResponse->xml['orderadditionalinfo']['rc'];
        } else if ($checkStatusPayResponse->rcCode) {
            $paySchet->RCCode = $checkStatusPayResponse->rcCode;
        }

        $paySchet->save(false);

        $this->getNotificationsService()->sendPostbacks($paySchet);
        return $paySchet;
    }
}
