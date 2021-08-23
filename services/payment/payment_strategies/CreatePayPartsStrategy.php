<?php


namespace app\services\payment\payment_strategies;


use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\mfo\MfoPayLkCreateStrategy;

class CreatePayPartsStrategy extends MfoPayLkCreateStrategy
{

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    protected function getUslugatovar()
    {
        return $this->payForm->partner
            ->getUslugatovars()
            ->where([
                'IsCustom' => $this->isAftGate() ? UslugatovarType::POGASHATFPARTS : UslugatovarType::POGASHECOMPARTS,
                'IsDeleted' => 0,
            ])
            ->one();
    }

}
