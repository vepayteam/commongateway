<?php

namespace app\services;

use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\PayschetPart;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\FailedRecurrentPaymentException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\jobs\RecurrentPayJob;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\RefreshStatusPayStrategy;
use app\services\recurrentPaymentPartsService\dataObjects\PaymentData;
use app\services\recurrentPaymentPartsService\PaymentException;
use yii\base\Component;

/**
 * Сервис для автоплатежа с разбивкой.
 */
class RecurrentPaymentPartsService extends Component
{
    /**
     * Полуение номера карты.
     *
     * @param Cards $card
     * @return string
     * @throws PaymentException
     */
    protected function getCardNumber(Cards $card): string
    {
        $panToken = $card->panToken;
        if ($panToken === null) {
            throw new PaymentException("Card (ID:{$card->ID}) has no pan token.", PaymentException::NO_PAN_TOKEN);
        }
        if (empty($panToken->EncryptedPAN)) {
            throw new PaymentException("Card (ID:{$card->ID}) is expired.", PaymentException::CARD_EXPIRED);
        }
        $cardNumber = (new CardToken())->GetCardByToken($panToken->ID);
        if (empty($cardNumber)) {
            throw new PaymentException("Empty card (ID:{$card->ID}).", PaymentException::EMPTY_CARD);
        }

        return $cardNumber;
    }

    /**
     * Инициализация оплаты.
     *
     * Выполняет необходимые проверки условий и входных данных, затем создает записи в БД
     * для последующего выполнения операций олаты.
     *
     * @param Partner $partner
     * @param PaymentData $paymentData
     * @return PaySchet
     * @throws PaymentException
     */
    public function createPayment(Partner $partner, PaymentData $paymentData): PaySchet
    {
        // Проверка карты / полуение номера карты
        $card = Cards::findOne(['ID' => $paymentData->getCardId()]);
        $cardNumber = $this->getCardNumber($card);

        $uslugatovar = $this->findUslugatovar($partner);

        // Инициализация банк-адаптера
        $bankAdapterBuilder = new BankAdapterBuilder();
        try {
            $bankAdapterBuilder->buildByBank($partner, $uslugatovar, $card->bank);
        } catch (GateException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new PaymentException("Gate not found (Partner ID:{$partner->ID}).", PaymentException::NO_GATE);
        }
        $bankAdapter = $bankAdapterBuilder->getBankAdapter();

        // Создание токена, если его нет
        $expires = $card->getMonth() . $card->getYear();
        $cartToken = new CardToken();
        $token = $cartToken->CheckExistToken($card->CardNumber, $expires);
        if ($token == 0) {
            $token = $cartToken->CreateToken($card->CardNumber, $expires, $card->CardHolder);
        }

        // Создание PaySchet
        $paySchet = new PaySchet();
        $paySchet->Status = PaySchet::STATUS_NOT_EXEC;
        $paySchet->IdKard = $card->ID;
        $paySchet->CardNum = Cards::MaskCard($cardNumber);
        $paySchet->CardHolder = mb_substr($card->CardHolder, 0, 99);
        $paySchet->CardExp = $expires;
        $paySchet->IdShablon = $token;
        $paySchet->Bank = $bankAdapter->getBankId();
        $paySchet->IdUsluga = $uslugatovar->ID;
        $paySchet->IdOrg = $partner->ID;
        $paySchet->Extid = $paymentData->getExternalId();
        $paySchet->QrParams = $paymentData->getDescription();
        $paySchet->SummPay = $paymentData->getTotalAmountFractional();
        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->UserUrlInform = $uslugatovar->UrlInform;
        $paySchet->sms_accept = 1;
        $paySchet->FIO = $paymentData->getFullname();
        $paySchet->Dogovor = $paymentData->getDoumentId();
        $paySchet->save(false);

        // Сохранение частей
        foreach ($paymentData->getParts() as $part) {
            $payschetPart = new PayschetPart();
            $payschetPart->PayschetId = $paySchet->ID;
            $payschetPart->PartnerId = $part->getPartnerId();
            $payschetPart->Amount = $part->getAmountFractional();
            $payschetPart->save(false);
        }

        return $paySchet;
    }

    /**
     * Оплата.
     *
     * Производит списание с карты.
     *
     * @param PaySchet $paySchet
     * @throws PaymentException
     * @see RecurrentPayJob::execute()
     */
    public function executePayment(PaySchet $paySchet)
    {
        // Создаем форму для легаси компонента
        $autoPayForm = new AutoPayForm();
        $autoPayForm->amount = $paySchet->SummPay;
        $autoPayForm->document_id = $paySchet->Dogovor;
        $autoPayForm->fullname = $paySchet->FIO;
        $autoPayForm->extid = $paySchet->Extid;
        $autoPayForm->descript = '';
        $autoPayForm->card = $paySchet->IdKard;
        $autoPayForm->paySchet = $paySchet;
        $autoPayForm->partner = $paySchet->partner;
        $autoPayForm->getCard()->CardNumber = $this->getCardNumber($autoPayForm->getCard());

        // Инициализация банк-адаптера
        $bankAdapterBuilder = new BankAdapterBuilder();
        try {
            $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank);
        } catch (GateException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new PaymentException("Gate not found (PaySchet ID:{$paySchet->ID}).", PaymentException::NO_GATE);
        }
        $bankAdapter = $bankAdapterBuilder->getBankAdapter();

        // Списание с карты.
        try {
            $bankAdapterResponse = $bankAdapter->recurrentPay($autoPayForm);
        } catch (\Exception $e) {
            \Yii::$app->errorHandler->logException($e);

            if ($e instanceof FailedRecurrentPaymentException) {
                $paySchet->RCCode = $e->getRcCode();
            }

            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->ErrorInfo = $e->getMessage();
            $paySchet->save(false);
            throw new PaymentException(
                "Bank exception (PaySchet ID:{$paySchet->ID}): " . $e->getMessage(),
                PaymentException::BANK_EXCEPTION);
        }

        $paySchet->ExtBillNumber = $bankAdapterResponse->transac;
        if ($bankAdapterResponse->status == BaseResponse::STATUS_DONE) {
            $paySchet->RRN = $bankAdapterResponse->rrn;
            $paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
            $paySchet->ErrorInfo = 'Ожидается обновление статуса';
            $paySchet->save(false);
        } else {
            $paySchet->ErrorInfo = $bankAdapterResponse->message;
            $paySchet->Status = PaySchet::STATUS_ERROR;
            $paySchet->save(false);
            $message = $bankAdapterResponse->status == BaseResponse::STATUS_ERROR
                ? "Bank response has error status (PaySchet ID:{$paySchet->ID})."
                : "Invalid bank response status (PaySchet ID:{$paySchet->ID}).";
            \Yii::warning(__CLASS__ . '::executePayment(): ' . $message);
            throw new PaymentException($message, PaymentException::BANK_EXCEPTION);
        }
    }

    /**
     * Обновление статуса оплаты.
     *
     * Получает статус операции списания от банка, выполненной в {@see executePayment()},
     * и обновляет статус соответствующей записи (PaySchet) в БД.
     *
     * @param PaySchet $paySchet
     * @return int Возвращает статус оплаты.
     * @throws GateException
     * @throws \yii\db\Exception
     * @see RefreshStatusPayJob::execute()
     */
    public function updatePaymentStatus(PaySchet $paySchet): int
    {
        /** @todo Заменить "стратегии" на сервисы */
        $okPayForm = new OkPayForm();
        $okPayForm->IdPay = $paySchet->ID;
        $refreshStatusPayStrategy = new RefreshStatusPayStrategy($okPayForm);
        $paySchet = $refreshStatusPayStrategy->exec();

        $status = $paySchet->Status;

        if ($paySchet->Status == PaySchet::STATUS_WAITING) {
            $paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
            $paySchet->ErrorInfo = 'Ожидает запрос статуса';
            $paySchet->save(false);
        }

        return $status;
    }

    /**
     * @param Partner $partner
     * @return Uslugatovar|null
     * @throws PaymentException
     */
    public function findUslugatovar(Partner $partner): ?Uslugatovar
    {
        /** @var Uslugatovar|null $uslugatovar */
        $uslugatovar = $partner
            ->getUslugatovars()
            ->andWhere([
                'IsCustom' => UslugatovarType::AVTOPLATECOMPARTS,
                'IsDeleted' => 0,
            ])
            ->one();

        if ($uslugatovar === null) {
            throw new PaymentException('Uslugatovar not found.', PaymentException::NO_USLUGATOVAR);
        }

        return $uslugatovar;
    }
}