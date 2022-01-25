<?php

namespace app\services;

use app\services\compensationService\CompensationException;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\RefundPayResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\RefundPayException;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\jobs\RefundPayJob;
use app\services\payment\models\PaySchet;
use app\services\paymentService\CreateRefundException;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class PaymentService extends Component
{
    /**
     * Reverses the specified payment.
     *
     * @param PaySchet $paySchet
     * @throws GateException
     * @throws RefundPayException
     * @see RefundPayJob::execute()
     */
    public function reverse(PaySchet $paySchet)
    {
        $refundPayResponse = $this->bankRefundPay($paySchet);

        if ($refundPayResponse->status == BaseResponse::STATUS_DONE) {
            $paySchet->Status = PaySchet::STATUS_CANCEL;
            $paySchet->ErrorInfo = 'Платеж отменен';
        } else {
            $paySchet->ErrorInfo = $refundPayResponse->message;
        }
        $paySchet->save(false);
    }

    /**
     * Creates an additional refund payment {@see PaySchet} for the specified source payment.
     *
     * @param PaySchet $sourcePaySchet The payment to refund.
     * @param int|null $amount Amount of partial refund in fractional part of currency e.g. cents. Full refund if NULL.
     * @return PaySchet Refund payment.
     * @throws CreateRefundException
     */
    public function createRefundPayment(PaySchet $sourcePaySchet, ?int $amount = null): PaySchet
    {
        /** @see PaySchet::refundNumber */
        $refundNumber = (int)max(ArrayHelper::getColumn($sourcePaySchet->refunds, 'refundNumber') + [0]) + 1;

        if ($amount === null) {
            $amount = $sourcePaySchet->getSummFull();
        }

        /** {@see PaySchet::SummPay} */
        $refundedAmount = array_sum(ArrayHelper::getColumn($sourcePaySchet->refunds, 'SummPay'));
        if (($amount + $refundedAmount) > $sourcePaySchet->getSummFull()) {
            throw new CreateRefundException('Amount exceeded.', CreateRefundException::AMOUNT_EXCEEDED);
        }


        $refundPayschet = new PaySchet($sourcePaySchet->getAttributes());
        $refundPayschet->Status = PaySchet::STATUS_NOT_EXEC;
        $refundPayschet->ErrorInfo = null;
        $refundPayschet->SummPay = $amount;
        $refundPayschet->Extid = "{$sourcePaySchet->Extid} R{$refundNumber}";
        $refundPayschet->RefundSourceId = $sourcePaySchet->ID;

        // see VPBC-960
        $refundPayschet->ID = null;
        $refundPayschet->ComissSumm = 0;
        $refundPayschet->MerchVozn = 0;
        $refundPayschet->BankComis = 0;
        $refundPayschet->Version3DS = null;
        $refundPayschet->IsNeed3DSVerif = null;
        $refundPayschet->DsTransId = null;
        $refundPayschet->Eci = null;
        $refundPayschet->AuthValue3DS = null;
        $refundPayschet->CardRefId3DS = null;
        $refundPayschet->UrlFormPay = null;
        $refundPayschet->UserUrlInform = null;
        $refundPayschet->UserKeyInform = null;
        $refundPayschet->SuccessUrl = null;
        $refundPayschet->FailedUrl = null;
        $refundPayschet->CancelUrl = null;
        $refundPayschet->PostbackUrl = null;
        $refundPayschet->PostbackUrl_v2 = null;

        try {
            $refundPayschet->save(false);
        } catch (GateException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new CreateRefundException('Gate not found.', CreateRefundException::GATE_NOT_FOUND);
        } catch (CompensationException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new CreateRefundException('Compensation error: ' . $e->getMessage(),
                CreateRefundException::COMPENSATION_ERROR);
        }

        return $refundPayschet;
    }

    /**
     * Executes refund.
     *
     * @param PaySchet $refundPayschet
     * @throws GateException
     * @throws RefundPayException
     * @see RefundPayJob::execute()
     */
    public function executRefundPayment(PaySchet $refundPayschet)
    {
        $refundPayResponse = $this->bankRefundPay($refundPayschet);

        if ($refundPayResponse->status == BaseResponse::STATUS_DONE) {
            $refundPayschet->Status = PaySchet::STATUS_REFUND_DONE;
            $refundPayschet->ErrorInfo = 'Возврат произведен.';
        } elseif(BaseResponse::STATUS_CREATED){
            $refundPayschet->Status = PaySchet::STATUS_WAITING;
            $refundPayschet->ErrorInfo = 'Возврат произведен.';
        } else {
            $refundPayschet->Status = PaySchet::STATUS_ERROR;
            $refundPayschet->ErrorInfo = $refundPayResponse->message;
        }
        $refundPayschet->save(false);
    }

    /**
     * Refund: creation and execution.
     *
     * @param PaySchet $paySchet
     * @param int|null $amountFractional
     * @throws GateException
     * @throws CreateRefundException
     * @throws RefundPayException
     */
    public function refund(PaySchet $paySchet, ?int $amountFractional = null)
    {
        $refundPayschet = $this->createRefundPayment($paySchet, $amountFractional);
        $this->executRefundPayment($refundPayschet);
    }

    /**
     * @param PaySchet $paySchet
     * @return RefundPayResponse
     * @throws GateException
     * @throws RefundPayException
     */
    private function bankRefundPay(PaySchet $paySchet): RefundPayResponse
    {
        return (new BankAdapterBuilder())
            ->build($paySchet->partner, $paySchet->uslugatovar)
            ->getBankAdapter()
            ->refundPay(new RefundPayForm(['paySchet' => $paySchet]));
    }
}