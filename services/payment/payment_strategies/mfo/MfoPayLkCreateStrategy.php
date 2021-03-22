<?php


namespace app\services\payment\payment_strategies\mfo;


use app\services\payment\banks\Banks;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\merchant\MerchantPayCreateStrategy;

class MfoPayLkCreateStrategy extends MerchantPayCreateStrategy
{
    const AFT_MIN_SUMM = 120000;

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
            ->andWhere(['<=', 'MinSumm', $this->payForm->amount])
            ->andWhere(['>=', 'MaxSumm', $this->payForm->amount])
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

        if($this->payForm->amount < $this->getAftMinSum()) {
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

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getAftMinSum()
    {
        /** @var PartnerBankGate $gate */
        $gate = PartnerBankGate::find()->where([
                'PartnerId' => $this->payForm->partner->ID,
                'TU' => UslugatovarType::POGASHECOM,
                'Enable' => 1
            ])
            ->orderBy('Priority DESC')
            ->one();

        if(!$gate) {
            throw new \Exception('Нет шлюза');
        }

        $bankAdapter = Banks::getBankAdapter($gate->BankId);
        return $bankAdapter->getAftMinSum();
    }
}
