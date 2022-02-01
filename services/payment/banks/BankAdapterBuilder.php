<?php

namespace app\services\payment\banks;

use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\Bank;
use app\services\payment\models\Currency;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\models\repositories\CurrencyRepository;
use Yii;

class BankAdapterBuilder
{
    /** @var Partner */
    protected $partner;
    /** @var Uslugatovar */
    protected $uslugatovar;

    /** @var PartnerBankGate */
    protected $partnerBankGate;

    /** @var IBankAdapter */
    protected $bankAdapter;
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;

    public function __construct()
    {
        $this->currencyRepository = new CurrencyRepository();
    }

    /**
     * @param Partner $partner
     * @param Uslugatovar $uslugatovar
     * @param Currency|null $currency
     * @return BankAdapterBuilder
     * @throws GateException
     */
    public function build(
        Partner $partner,
        Uslugatovar $uslugatovar,
        Currency $currency = null
    ): BankAdapterBuilder {
        if (!$currency) {
            $currency = $this->currencyRepository->getDefaultMainCurrency();
        }
        $this->partner = $partner;
        $this->uslugatovar = $uslugatovar;
        $this->partnerBankGate = $partner
            ->getBankGates()
            ->where([
                'TU' => $uslugatovar->IsCustom,
                'Enable' => 1,
                'CurrencyId' => $currency->Id
            ])
            ->orderBy('Priority DESC')
            ->one();

        if (!$this->partnerBankGate) {
            throw new GateException(
                "Нет шлюза. partnerId=$partner->ID uslugatovarId=$uslugatovar->ID currency=$currency->Code"
            );
        }
        return $this->buildAdapter();
    }

    // TODO: не используется
    public function buildByPartnerBankGate(Partner $partner, PartnerBankGate $partnerBankGate) {
        $this->partner = $partner;
        $this->partnerBankGate = $partnerBankGate;

        return $this->buildAdapter();
    }

    /**
     * @param Partner $partner
     * @param Uslugatovar $uslugatovar
     * @param Bank $bank
     * @return $this
     * @throws GateException
     */
    public function buildByBank(Partner $partner, Uslugatovar $uslugatovar, Bank $bank, Currency $currency = null)
    {
        if(is_null($currency)) {
            $currency = Currency::findOne(['Code' => Currency::MAIN_CURRENCY]);
        }

        $this->partner = $partner;
        $this->uslugatovar = $uslugatovar;
        $this->partnerBankGate = $partner
            ->getBankGates()
            ->where([
                'BankId' => $bank->ID,
                'TU' => $uslugatovar->IsCustom,
                'Enable' => 1,
                'CurrencyId' => $currency->Id,
            ])->orderBy('Priority DESC')->one();

        if (!$this->partnerBankGate) {
            Yii::error(Yii::$app->request->getRawBody(), 'buildByBank');
            throw new GateException("Нет шлюза. partnerId=$partner->ID uslugatovarId=$uslugatovar->ID bankId=$bank->ID");
        }
        return $this->buildAdapter();
    }

    /**
     * @param Partner $partner
     * @param Bank $bank
     * @return $this
     * @throws GateException
     */
    public function buildByBankId(Partner $partner, Bank $bank): BankAdapterBuilder
    {
        $this->partner = $partner;
        $this->partnerBankGate = $partner
            ->getBankGates()
            ->where([
                'BankId' => $bank->ID,
                'Enable' => 1
            ])
            ->orderBy('Priority DESC')
            ->one();

        if (!$this->partnerBankGate) {
            throw new GateException(sprintf(
                "Нет шлюза. partnerId=%d bankId=%d",
                $partner->ID,
                $bank->ID
            ));
        }
        return $this->buildAdapter();
    }

    /**
     * @param Bank $bank
     * @return $this
     * @throws GateException
     */
    public function buildByBankOnly(Bank $bank): BankAdapterBuilder
    {
        $this->partnerBankGate = PartnerBankGate::find()
            ->where([
                'BankId' => $bank->ID,
                'Enable' => 1
            ])
            ->orderBy('Priority DESC')
            ->one();

        if (!$this->partnerBankGate) {
            throw new GateException(sprintf(
                "Нет шлюза. bankId=%d",
                $bank->ID
            ));
        }
        return $this->buildAdapter();
    }

    /**
     * @return $this
     * @throws GateException
     */
    protected function buildAdapter(): BankAdapterBuilder
    {
        try {
            $this->bankAdapter = Banks::getBankAdapter($this->partnerBankGate->BankId);
        } catch (\Exception $e) {
            Yii::error([$e->getMessage(), $e->getTrace(), $e->getFile()], 'buildAdapter');
            throw new GateException('Cant get Bank Adapter');
        }
        $this->bankAdapter->setGate($this->partnerBankGate);
        return $this;
    }

    /**
     * @return IBankAdapter
     */
    public function getBankAdapter()
    {
        return $this->bankAdapter;
    }

    /**
     * @return Uslugatovar
     */
    public function getUslugatovar()
    {
        return $this->uslugatovar;
    }

    /**
     * @return PartnerBankGate
     */
    public function getPartnerBankGate(): PartnerBankGate
    {
        return $this->partnerBankGate;
    }
}
