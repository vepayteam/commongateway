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
        $paySchet->PostbackUrl = $invoiceObject->postbackUrl;
        $paySchet->PostbackUrl_v2 = $invoiceObject->postbackUrlV2;

        $paySchet->sms_accept = 1;
        $paySchet->Dogovor = $invoiceObject->documentId;
        $paySchet->TimeElapsed = $invoiceObject->timeoutSeconds ?? self::DEFAULT_TIMEOUT;
        $paySchet->IPAddressUser = $invoiceObject->ip;

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
        /** @var Uslugatovar $uslugatovar */
        $uslugatovar = $partner
            ->getUslugatovars()
            ->andWhere([
                'IsCustom' => $this->isAftGate($partner, $amountFractional) ? UslugatovarType::POGASHATF : UslugatovarType::POGASHECOM,
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
     * @return bool
     * @throws InvoiceCreateException
     * @todo Логика из легаси. Добавить пояснение к блокам кода внутри метода что там происходит и зачем.
     * @see MfoPayLkCreateStrategy::isAftGate()
     */
    protected function isAftGate(Partner $partner, $amountFractional): bool
    {
        if ($partner->IsAftOnly) {
            return true;
        }

        /** @var PartnerBankGate $gate */
        $gate = $partner
            ->getBankGates()
            ->andWhere(['TU' => UslugatovarType::POGASHECOM, 'Enable' => 1])
            ->orderBy('Priority DESC')
            ->one();
        if (!$gate) {
            throw new InvoiceCreateException('Gate not found.', InvoiceCreateException::NO_GATE);
        }
        $aftMinSum = Banks::getBankAdapter($gate->BankId)->getAftMinSum();
        if ($amountFractional < $aftMinSum) {
            return false;
        }

        if ($partner->getBankGates()->andWhere(['TU' => UslugatovarType::POGASHATF, 'Enable' => 1])->exists()) {
            return true;
        }

        return false;
    }
}