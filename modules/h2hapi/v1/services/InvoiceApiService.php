<?php

namespace app\modules\h2hapi\v1\services;

use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\modules\h2hapi\v1\objects\InvoiceObject;
use app\modules\h2hapi\v1\services\invoiceApiService\InvoiceCreateException;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\Banks;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\Currency;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\merchant\MerchantPayCreateStrategy;
use app\services\payment\payment_strategies\mfo\MfoPayLkCreateStrategy;
use yii\base\Component;

/**
 * Сервис для работы со Счетом.
 */
class InvoiceApiService extends Component
{
    private const DEFAULT_TIMEOUT = 15 * 60;

    /**
     * @param Partner $partner
     * @param PaySchet $paySchet
     * @return InvoiceObject
     */
    public function get(Partner $partner, PaySchet $paySchet): InvoiceObject
    {
        return (new InvoiceObject($partner))->mapPaySchet($paySchet);
    }

    /**
     * @param Partner $partner
     * @param InvoiceObject $invoiceObject
     * @return PaySchet
     * @throws InvoiceCreateException
     * @see MfoPayLkCreateStrategy
     * @see MerchantPayCreateStrategy::exec()
     */
    public function create(Partner $partner, InvoiceObject $invoiceObject): PaySchet
    {
        /** @var Currency $currency */
        $currency = Currency::findOne(['Code' => $invoiceObject->currencyCode]);
        $uslugatovar = $this->findUslugatovar($partner, $invoiceObject->amountFractional);

        $bankAdapterBuilder = new BankAdapterBuilder();
        try {
            $bankAdapterBuilder->build($partner, $uslugatovar, $currency);
        } catch (GateException $e) {
            \Yii::$app->errorHandler->logException($e);
            throw new InvoiceCreateException('Gate not found.', InvoiceCreateException::NO_GATE);
        }

        $paySchet = new PaySchet();
        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $uslugatovar->ID;
        $paySchet->IdOrg = $partner->ID;
        $paySchet->Extid = $invoiceObject->externalId;
        $paySchet->QrParams = $invoiceObject->description;
        $paySchet->SummPay = $invoiceObject->amountFractional;
        $paySchet->CurrencyId = $currency->Id;
        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->IsAutoPay = 0;
        $paySchet->UserUrlInform = $uslugatovar->UrlInform;

        $paySchet->SuccessUrl = $invoiceObject->successUrl;
        $paySchet->FailedUrl = $invoiceObject->failUrl;
        $paySchet->CancelUrl = $invoiceObject->cancelUrl;

        $paySchet->sms_accept = 1;
        $paySchet->Dogovor = $invoiceObject->documentId;
        $paySchet->TimeElapsed = $invoiceObject->timeoutSeconds ?? self::DEFAULT_TIMEOUT;

        if ($invoiceObject->client !== null) {
            $paySchet->FIO = $invoiceObject->client->fullName;
            $paySchet->UserEmail = $invoiceObject->client->email;
            $paySchet->AddressUser = $invoiceObject->client->address;
            $paySchet->PhoneUser = $invoiceObject->client->phone;
            $paySchet->LoginUser = $invoiceObject->client->login;
            $paySchet->ZipUser = $invoiceObject->client->zip;
        }

        $paySchet->save(false);

        return $paySchet;
    }

    /**
     * @param Partner $partner
     * @param int $amountFractional
     * @return Uslugatovar
     * @throws InvoiceCreateException
     * @see MfoPayLkCreateStrategy::getUslugatovar()
     */
    public function findUslugatovar(Partner $partner, int $amountFractional): Uslugatovar
    {
        if ($partner->IsMfo) {
            $uslugatovarTypeId = $this->getMfoUslugatovarTypeId($partner, $amountFractional);
        } else {
            $uslugatovarTypeId = UslugatovarType::H2H_ECOM;
        }

        /** @var Uslugatovar $uslugatovar */
        $uslugatovar = $partner
            ->getUslugatovars()
            ->andWhere([
                'IsCustom' => $uslugatovarTypeId,
                'IsDeleted' => 0,
            ])
            ->one();

        if ($uslugatovar === null) {
            throw new InvoiceCreateException('Uslugatovar not found.', InvoiceCreateException::NO_USLUGATOVAR);
        }

        return $uslugatovar;
    }

    /**
     * @param Partner $partner
     * @param $amountFractional
     * @return int
     * @throws InvoiceCreateException
     * @todo Логика из легаси. Оптимизировать алгоритм.
     * @see MfoPayLkCreateStrategy::isAftGate()
     */
    protected function getMfoUslugatovarTypeId(Partner $partner, $amountFractional): int
    {
        /**
         * - Ищется приоритетный шлюз для ECOM. Если не найден - ошибка отсутствия шлюза.
         * - Создается BankAdapter для банка шлюза. Если сумма счета меньше минимальной суммы для AFT в этом банке,
         * то используем ECOM.
         * - Иначе ищем шлюз для AFT и, если находим, - используем AFT.
         */

        if ($partner->IsAftOnly) {
            return UslugatovarType::H2H_POGASH_AFT;
        }

        /** @var PartnerBankGate $gate */
        $gate = $partner
            ->getBankGates()
            ->andWhere(['TU' => UslugatovarType::H2H_POGASH_ECOM, 'Enable' => 1])
            ->orderBy('Priority DESC')
            ->one();
        if (!$gate) {
            throw new InvoiceCreateException('Gate not found.', InvoiceCreateException::NO_GATE);
        }
        $aftMinSum = Banks::getBankAdapter($gate->BankId)->getAftMinSum();
        if ($amountFractional < $aftMinSum) {
            return UslugatovarType::H2H_POGASH_ECOM;
        }

        if ($partner->getBankGates()->andWhere(['TU' => UslugatovarType::H2H_POGASH_AFT, 'Enable' => 1])->exists()) {
            return UslugatovarType::H2H_POGASH_AFT;
        }

        return UslugatovarType::H2H_POGASH_ECOM;
    }
}