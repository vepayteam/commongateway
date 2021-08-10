<?php

namespace app\modules\h2hapi\v1\services;

use app\models\payonline\Cards;
use app\modules\h2hapi\v1\objects\PaymentObject;
use app\modules\h2hapi\v1\services\paymentApiService\PaymentCreateException;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\MerchantRequestAlreadyExistsException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\models\PaySchet;
use yii\base\Component;

/**
 * Сервис для оплаты по Счету.
 */
class PaymentApiService extends Component
{
    /**
     * @param PaySchet $paySchet
     * @param PaymentObject $paymentObject
     * @return PaymentObject
     * @throws PaymentCreateException
     */
    public function create(PaySchet $paySchet, PaymentObject $paymentObject): PaymentObject
    {
        $cardObject = $paymentObject->card;

        if ($paySchet->isOld()) {
            throw new PaymentCreateException('Invoice expired.', PaymentCreateException::INVOICE_EXPIRED);
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        try {
            $bankAdapterBuilder->build($paySchet->partner, $paySchet->uslugatovar, $paySchet->currency);
        } catch (GateException $e) {
            throw new PaymentCreateException('Gate not found.', PaymentCreateException::NO_GATE);
        }

        $paySchet->CardNum = Cards::MaskCard($cardObject->number);
        $paySchet->CardType = Cards::GetCardBrand(Cards::GetTypeCard($cardObject->number));
        $paySchet->CardHolder = mb_substr($cardObject->holder, 0, 99);
        $paySchet->CardExp = $cardObject->expires;

        $paySchet->save(false);

        /**
         * @todo Легаси. Удалить CreatePayForm.
         */
        $createPayForm = new CreatePayForm();
        $createPayForm->CardNumber = $cardObject->number;
        $createPayForm->CardExp = $cardObject->expires;
        $createPayForm->CardHolder = $cardObject->holder;
        $createPayForm->CardCVC = $cardObject->cvc;
        $createPayForm->IdPay = $paySchet->ID;
        $createPayForm->afterValidate();

        try {
            $createPayResponse = $bankAdapterBuilder->getBankAdapter()->createPay($createPayForm);
        } catch (BankAdapterResponseException $e) {
            throw new PaymentCreateException('Bank adapter error.', PaymentCreateException::BANK_ADAPTER_ERROR, $e->getMessage());
        } catch (CreatePayException $e) {
            throw new PaymentCreateException('Create pay error.', PaymentCreateException::CREATE_PAY_ERROR, $e->getMessage());
        } catch (Check3DSv2Exception | MerchantRequestAlreadyExistsException $e) {
            /** @todo Реализовать корректную обработку ошибок ТКБ */
            throw new PaymentCreateException('TKB Error', PaymentCreateException::TKB_ERROR, $e->getMessage());
        }

        if (in_array($createPayResponse->status, [BaseResponse::STATUS_CANCEL, BaseResponse::STATUS_ERROR])) {
            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->ErrorInfo = mb_substr($createPayResponse->message, 0, 250);
        } else {
            $paySchet->ExtBillNumber = $createPayResponse->transac;
            $paySchet->Version3DS = $createPayResponse->vesion3DS;
            $paySchet->IsNeed3DSVerif = ($createPayResponse->isNeed3DSVerif ? 1 : 0);
            $paySchet->AuthValue3DS = $createPayResponse->authValue;
            $paySchet->DsTransId = $createPayResponse->dsTransId;
            $paySchet->Eci = $createPayResponse->eci;
            $paySchet->CardRefId3DS = $createPayResponse->cardRefId;
        }
        $paySchet->save(false);

        $paySchet->refresh();
        $paymentObject = (new PaymentObject())->mapPaySchet($paySchet);
        $paymentObject->acsUrl = $createPayResponse->url;

        return $paymentObject;
    }

    /**
     * @param PaySchet $paySchet
     * @return bool
     */
    public function hasPayment(PaySchet $paySchet): bool
    {
        return $paySchet->CardNum !== null;
    }
}