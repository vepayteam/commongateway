<?php


namespace app\services\payment\payment_strategies\mfo;


use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\OutPayaccForm;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;

class MfoVyvodVoznagStrategy extends MfoOutPayaccStrategy
{
    /**
     * @return Uslugatovar|null
     */
    protected function getUslugatovar()
    {
        return $this
            ->outPayaccForm
            ->partner
            ->getUslugatovars()
            ->where([
                'IsCustom' => TU::$VYPLATVOZN,
                'IsDeleted' => 0,
            ])
            ->one();
    }

}
