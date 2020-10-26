<?php


namespace app\services\payment\payment_strategies;


use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\DonePayForm;
use app\services\payment\models\PaySchet;

class DonePayStrategy
{
    /** @var DonePayForm */
    protected $donePayForm;

    /** @var array|null */
    protected $donePayResponse;

    /**
     * DonePayStrategy constructor.
     * @param DonePayForm $donePayForm
     */
    public function __construct(DonePayForm $donePayForm)
    {
        $this->donePayForm = $donePayForm;
    }

    public function exec()
    {
        $paySchet = $this->donePayForm->getPaySchet();

        if($paySchet->Status == PaySchet::STATUS_WAITING && !empty($this->donePayForm->md)) {

            $bankAdapterBuilder = new BankAdapterBuilder();
            $bankAdapterBuilder->build($paySchet->partner, $paySchet->uslugatovar);

            $this->donePayResponse = $bankAdapterBuilder->getBankAdapter()->confirm($this->donePayForm);
        }

        return $paySchet;
    }

    /**
     * @return array|null
     */
    public function getDonePayResponse()
    {
        return $this->donePayResponse;
    }

}