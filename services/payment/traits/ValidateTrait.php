<?php


namespace app\services\payment\traits;


use app\models\payonline\Uslugatovar;
use app\services\payment\interfaces\AmountFormInterface;
use app\services\payment\models\PaySchet;

trait ValidateTrait
{

    /**
     * @param AmountFormInterface $amountForm
     * @param Uslugatovar $uslugatovar
     * @return array
     */
    public function validatePaySchetWithUslugatovar(AmountFormInterface $amountForm, Uslugatovar $uslugatovar)
    {
        $errors = [];
        if($amountForm->getAmount() < $uslugatovar->MinSumm) {
            $errors[] = 'Минимальная сумма платежа ' . ($uslugatovar->MinSumm / 100) . ' руб.';
        }
        if($amountForm->getAmount() > $uslugatovar->MaxSumm) {
            $errors[] = 'Максимальная сумма платежа ' . ($uslugatovar->MaxSumm / 100) . ' руб.';
        }
        return $errors;
    }

}
