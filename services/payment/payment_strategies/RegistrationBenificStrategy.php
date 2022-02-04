<?php


namespace app\services\payment\payment_strategies;


use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\RegistrationBenificResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\RegistrationBenificForm;
use app\services\payment\models\UslugatovarType;

class RegistrationBenificStrategy
{
    /** @var  RegistrationBenificForm **/
    protected $registrationBenificForm;

    /**
     * RegistrationBenificStrategy constructor.
     */
    public function __construct(RegistrationBenificForm $registrationBenificForm)
    {
        $this->registrationBenificForm = $registrationBenificForm;
    }

    /**
     * @return RegistrationBenificResponse
     * @throws BankAdapterResponseException
     * @throws GateException
     */
    public function exec()
    {
        $uslugatovar = $this->getUslugatovar();
        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        try {
            $bankAdapterBuilder->build($this->registrationBenificForm->partner, $uslugatovar);
        } catch (GateException $e) {
            throw $e;
        }

        if(empty($bankAdapterBuilder->getPartnerBankGate()->SchetNumber)) {
            throw new GateException('В адаптере не указан номер счета');
        }

        $bankAdapter = $bankAdapterBuilder->getBankAdapter();
        /** @var RegistrationBenificResponse $registrationBenificResponse */
        $registrationBenificResponse = $bankAdapter->registrationBenific($this->registrationBenificForm);

        if($registrationBenificResponse->status != BaseResponse::STATUS_DONE) {
            throw new BankAdapterResponseException('Ошибка запроса');
        }

        return $registrationBenificResponse;
    }

    /**
     * @return UslugatovarType|null
     */
    protected function getUslugatovar()
    {
        return $this
            ->registrationBenificForm
            ->partner
            ->getUslugatovars()
            ->where([
                'IsCustom' => UslugatovarType::REGISTRATION_BENIFIC,
                'IsDeleted' => 0,
            ])
            ->one();
    }
}
