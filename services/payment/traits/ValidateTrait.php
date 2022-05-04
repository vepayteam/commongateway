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
            $errors[] = 'Значение «Amount» не должно быть меньше ' . ($uslugatovar->MinSumm / 100);
        }
        if($amountForm->getAmount() > $uslugatovar->MaxSumm) {
            $errors[] = 'Значение «Amount» не должно превышать ' . ($uslugatovar->MaxSumm / 100);
        }
        return $errors;
    }

}
