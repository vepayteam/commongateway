<?php

namespace app\services;

use app\services\compensationService\CompensationException;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\RefundPayException;
use app\services\payment\forms\RefundPayForm;
use app\services\payment\jobs\RefreshStatusPayJob;
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
        $this->refundInternal(
            $paySchet,
            PaySchet::STATUS_REVERSE_DONE,
            'Платеж отменен'
        );
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
        $refundedAmount = $sourcePaySchet->refundedAmount;
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
        $this->refundInternal(
            $refundPayschet,
            PaySchet::STATUS_REFUND_DONE,
            'Возврат произведен.'
        );
    }

    /**
     * @param PaySchet $refundPayschet
     * @param $successStatus
     * @param $successErrorInfo
     * @throws GateException
     * @throws RefundPayException
     */
    private function refundInternal(PaySchet $refundPayschet, $successStatus, $successErrorInfo)
    {
        $refundPayResponse = (new BankAdapterBuilder())
            ->build($refundPayschet->partner, $refundPayschet->uslugatovar)
            ->getBankAdapter()
            ->refundPay(new RefundPayForm(['paySchet' => $refundPayschet]));

        $refundPayschet->RefundType = $refundPayResponse->refundType;
        if ($refundPayResponse->transactionId) {
            $refundPayschet->ExtBillNumber = $refundPayResponse->transactionId;
        }
        if ($refundPayResponse->extId) {
            $refundPayschet->RefundExtId = $refundPayResponse->extId;
        }

        if ($refundPayResponse->status == BaseResponse::STATUS_DONE) {
            $refundPayschet->Status = PaySchet::getDoneStatusByRefundType($refundPayResponse->refundType);
            $refundPayschet->ErrorInfo = $successErrorInfo;

            if ($refundPayResponse->refundType === PaySchet::REFUND_TYPE_REVERSE) {
                $sourcePaySchet = $refundPayschet->refundSource;
                $sourcePaySchet->ErrorInfo = 'Операция отменена. Номер отмены: ' . $refundPayschet->ID;
                $sourcePaySchet->save(false);
            }
        } elseif ($refundPayResponse->status == BaseResponse::STATUS_CREATED) {
            /**
             * Logic copied from {@see RefundPayJob::execute()}.
             * @todo Add correct status processing.
             */
            $refundPayschet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
            $refundPayschet->ErrorInfo = $refundPayResponse->message;

            \Yii::$app->queue->push(new RefreshStatusPayJob([
                'paySchetId' => $refundPayschet->ID,
            ]));
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
}