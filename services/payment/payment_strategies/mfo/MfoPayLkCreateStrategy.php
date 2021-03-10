<?php


namespace app\services\payment\payment_strategies\mfo;


use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\merchant\MerchantPayCreateStrategy;

class MfoPayLkCreateStrategy extends MerchantPayCreateStrategy
{
    const AFT_MIN_SUMM = 1200;

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    protected function getUslugatovar()
    {
        return $this->payForm->partner
            ->getUslugatovars()
            ->where([
                'IsCustom' => $this->isAftGate() ? UslugatovarType::POGASHATF : UslugatovarType::POGASHECOM,
                'IsDeleted' => 0,
            ])
            ->one();
    }

    /**
     * @return bool
     */
    protected function isAftGate()
    {
        if($this->payForm->partner->IsAftOnly) {
            return true;
        }

        if($this->payForm->amount < self::AFT_MIN_SUMM) {
            return false;
        }
        if ($this->payForm->partner->getBankGates()->where([
            'TU' => UslugatovarType::POGASHATF,
            'Enable' => 1,
        ])->exists()) {
            return true;
        }

        return false;
    }
}
