<?php


namespace app\services\payment\banks;


use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\PartnerBankGate;

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
     * @param Partner $partner
     * @param Uslugatovar $uslugatovar
     * @throws GateException
     */
    public function build(Partner $partner, Uslugatovar $uslugatovar)
    {
        $this->partner = $partner;
        $this->uslugatovar = $uslugatovar;
        $this->partnerBankGate = $partner
            ->getBankGates()
            ->where([
                'TU' => $uslugatovar->IsCustom,
                'Enable' => 1
            ])->orderBy('Priority DESC')->one();

        if (!$this->partnerBankGate) {
            throw new GateException('Нет шлюза');
        }

        try {
            $this->bankAdapter = Banks::getBankAdapter($this->partnerBankGate->BankId);
        } catch (\Exception $e) {
            throw new GateException($e->getMessage());
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

}
