<?php


namespace app\services\payment\payment_strategies;


use app\models\PayschetPart;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\forms\CreatePayPartsForm;
use app\services\payment\models\PaySchet;
use app\services\payment\models\repositories\CurrencyRepository;
use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\mfo\MfoPayLkCreateStrategy;

class CreatePayPartsStrategy extends MfoPayLkCreateStrategy
{
    /** @var CreatePayPartsForm */
    protected $payForm;

    /**
     * CreatePayPartsStrategy constructor.
     */
    public function __construct(CreatePayPartsForm $createPayPartsForm)
    {
        $this->payForm = $createPayPartsForm;
        $this->currencyRepository = new CurrencyRepository();
    }

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

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     * @return PaySchet
     * @throws \app\services\payment\exceptions\CreatePayException
     */
    protected function createPaySchet(BankAdapterBuilder $bankAdapterBuilder): PaySchet
    {
        $paySchet = parent::createPaySchet($bankAdapterBuilder);
        $this->createPayParts($paySchet);
        return $paySchet;
    }

    /**
     * @param PaySchet $paySchet
     */
    protected function createPayParts(PaySchet $paySchet)
    {
        foreach ($this->payForm->parts as $part) {
            $payschetPart = new PayschetPart();
            $payschetPart->PayschetId = $paySchet->ID;
            $payschetPart->PartnerId = $part['merchant_id'];
            $payschetPart->Amount = $part['amount'];
            $payschetPart->save(false);
        }
    }

}
