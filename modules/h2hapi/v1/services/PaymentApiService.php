<?php

namespace app\modules\h2hapi\v1\services;

use app\controllers\PayController;
use app\models\payonline\Cards;
use app\modules\h2hapi\v1\objects\PaymentObject;
use app\modules\h2hapi\v1\services\paymentApiService\PaymentCreateException;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\data\ClientData;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\MerchantRequestAlreadyExistsException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\OkPayStrategy;
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
        if ($paySchet->isOld()) {
            throw new PaymentCreateException('Invoice expired.', PaymentCreateException::INVOICE_EXPIRED);
        }

        // Получаем bank adapter
        $bankAdapterBuilder = new BankAdapterBuilder();
        try {
            $bankAdapterBuilder->build($paySchet->partner, $paySchet->uslugatovar, $paySchet->currency);
        } catch (GateException $e) {
            throw new PaymentCreateException('Gate not found.', PaymentCreateException::NO_GATE);
        }
        $bankAdapter = $bankAdapterBuilder->getBankAdapter();

        $paySchet->IPAddressUser = $paymentObject->ip;
        $cardObject = $paymentObject->card;
        $paySchet->CardNum = Cards::MaskCard($cardObject->cardNumber);
        $paySchet->CardType = Cards::GetCardBrand(Cards::GetTypeCard($cardObject->cardNumber));
        $paySchet->CardHolder = mb_substr($cardObject->cardHolder, 0, 99);
        $paySchet->CardExp = $cardObject->expires;
        $paySchet->save(false);

        /** @todo Легаси. Удалить CreatePayForm. */
        $createPayForm = new CreatePayForm();
        $createPayForm->CardNumber = $cardObject->cardNumber;
        $createPayForm->CardExp = $cardObject->expires;
        $createPayForm->CardHolder = $cardObject->cardHolder;
        $createPayForm->CardCVC = $cardObject->cvc;
        $createPayForm->IdPay = $paySchet->ID;
        $createPayForm->afterValidate();

        // Запрос к банку
        try {
            $createPayResponse = $bankAdapter->createPay($createPayForm, new ClientData(
                $paymentObject->ip,
                $paymentObject->headerMap->userAgent ?? null,
                $paymentObject->headerMap->accept ?? null,
                $paymentObject->browserData->screenHeight ?? null,
                $paymentObject->browserData->screenWidth ?? null,
                $paymentObject->browserData->timezoneOffset ?? null,
                $paymentObject->browserData->windowHeight ?? null,
                $paymentObject->browserData->windowWidth ?? null,
                $paymentObject->browserData->language ?? null,
                $paymentObject->browserData->colorDepth ?? null,
                $paymentObject->browserData->javaEnabled ?? null
            ));
        } catch (BankAdapterResponseException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new PaymentCreateException('Bank adapter error.', PaymentCreateException::BANK_ADAPTER_ERROR, $e->getMessage());
        } catch (CreatePayException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new PaymentCreateException('Create pay error.', PaymentCreateException::CREATE_PAY_ERROR, $e->getMessage());
        } catch (Check3DSv2Exception|MerchantRequestAlreadyExistsException $e) {
            /** @todo Реализовать корректную обработку ошибок ТКБ */
            \Yii::$app->errorHandler->logException($e);
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
            $paySchet->save(false);

            // Если 3DS не требуется, завершаем все операции по оплате.
            if (!$paySchet->IsNeed3DSVerif) {
                /** @todo Зарефактроить confirm(), чтобы он принимал только те данные, которые ему нужны. */
                $donePayForm = new DonePayForm();
                $donePayForm->IdPay = $paySchet->ID;
                $bankAdapter->confirm($donePayForm);

                /** @todo Легаси */
                /** @see PayController::actionOrderok() */
                $okPayForm = new OkPayForm();
                $okPayForm->IdPay = $paySchet->ID;
                $okPayStrategy = new OkPayStrategy($okPayForm);
                $okPayStrategy->exec();
            }
        }
        $paySchet->save(false);

        $paySchet->refresh();
        $paymentObject = (new PaymentObject())->mapPaySchet($paySchet);
        $paymentObject->acsUrl = $paySchet->IsNeed3DSVerif ? $createPayResponse->url : null;

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