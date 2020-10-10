<?php


namespace app\models\bank;


use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\TU;

class BankGateBuilder
{
    /** @var BankGate */
    protected $gate;
    /** @var Partner */
    protected $partner;
    /** @var IBank */
    protected $bank;
    /** @var Uslugatovar */
    protected $uslugatovar;

    public function build(Partner $partner, Uslugatovar $uslugatovar)
    {
        $this->partner = $partner;
        $this->uslugatovar = $uslugatovar;


    }

    protected function selectBank()
    {
        if(TU::IsInPay($this->uslugatovar->IsCustom)) {

        }
    }

}
