<?php


namespace app\services\payment\payment_strategies;


use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\DonePayForm;
use app\services\payment\interfaces\Issuer3DSVersionInterface;
use app\services\payment\models\PaySchet;
use yii\web\NotFoundHttpException;

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
        // для случаев, если пользователь возвращается к нам без ИД счета, но с транзакцией
        if(!empty($this->donePayForm->trans)) {
            $paySchet = PaySchet::find()
                ->where(['ExtBillNumber' => $this->donePayForm->trans])
                ->orderBy('ID DESC')->one();
        } else {
            $paySchet = $this->donePayForm->getPaySchet();
        }

        if($paySchet && $paySchet->Status == PaySchet::STATUS_WAITING) {
            $bankAdapterBuilder = new BankAdapterBuilder();
            $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank);

            $this->donePayResponse = $bankAdapterBuilder->getBankAdapter()->confirm($this->donePayForm);
            return $paySchet;
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @return array|null
     */
    public function getDonePayResponse()
    {
        return $this->donePayResponse;
    }

}
