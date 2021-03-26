<?php


namespace app\services\payment\payment_strategies\mfo;


use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\OutPayaccForm;

class MfoOutPayaccStrategy
{
    /** @var OutPayaccForm */
    private $outPayaccForm;

    /**
     * @param OutPayaccForm $outPayaccForm
     */
    public function __construct(OutPayaccForm $outPayaccForm)
    {
        $this->outPayaccForm = $outPayaccForm;
    }

    public function exec()
    {
        $uslugatovar = $this->getUslugatovar();
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->outPayaccForm->partner, $uslugatovar);


    }

    /**
     * @return Uslugatovar|null
     */
    public function getUslugatovar()
    {
        return $this
            ->outPayaccForm
            ->partner
            ->getUslugatovars()
            ->where([
                'IsCustom' => TU::$TOSCHET,
                'IsDeleted' => 0,
            ])
            ->one();
    }

}
