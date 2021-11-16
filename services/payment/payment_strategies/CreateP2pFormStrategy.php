<?php


namespace app\services\payment\payment_strategies;


use app\models\payonline\Uslugatovar;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\CreateP2pForm;
use app\services\payment\models\Currency;
use app\services\payment\models\PaySchet;
use app\services\payment\models\repositories\CurrencyRepository;
use app\services\payment\models\UslugatovarType;

class CreateP2pFormStrategy
{
    /** @var CreateP2pForm */
    public $createP2pForm;
    /** @var CurrencyRepository */
    protected $currencyRepository;

    /**
     * CreateP2pFormStrategy constructor.
     * @param CreateP2pForm $createP2pForm
     */
    public function __construct(CreateP2pForm $createP2pForm)
    {
        $this->createP2pForm = $createP2pForm;
        $this->currencyRepository = new CurrencyRepository();
    }

    /**
     * @return PaySchet
     * @throws CreatePayException
     * @throws GateException
     */
    public function exec()
    {
        /** @var Uslugatovar $uslugatovar */
        $uslugatovar = $this->getUslugatovar();
        /** @var Currency $currency */
        $currency = $this->currencyRepository->getCurrency($this->createP2pForm->currency);

        if(!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }

        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->createP2pForm->partner, $uslugatovar, $currency);
        $paySchet = $this->createPaySchet($bankAdapterBuilder);
        return $paySchet;

    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     *
     * @return PaySchet
     * @throws CreatePayException
     * @throws \Exception
     */
    protected function createPaySchet(BankAdapterBuilder $bankAdapterBuilder): PaySchet
    {
        $paySchet = new PaySchet();
        $currencyRepository = new CurrencyRepository();
        $currency = $currencyRepository->getCurrency($this->createP2pForm->currency);
        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $bankAdapterBuilder->getUslugatovar()->ID;
        $paySchet->IdOrg = $this->createP2pForm->partner->ID;
        $paySchet->Extid = $this->createP2pForm->extid;
        $paySchet->QrParams = $this->createP2pForm->descript;
        $paySchet->SummPay = 0;
        $paySchet->CurrencyId = $currency->Id;
        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->IsAutoPay = 0;
        $paySchet->UserUrlInform = $bankAdapterBuilder->getUslugatovar()->UrlInform;
        $paySchet->TimeElapsed = $this->createP2pForm->timeout * 60;

        $paySchet->SuccessUrl = $this->createP2pForm->successurl;
        $paySchet->FailedUrl = $this->createP2pForm->failurl;
        $paySchet->CancelUrl = $this->createP2pForm->cancelurl;
        $paySchet->PostbackUrl = $this->createP2pForm->postbackurl;
        $paySchet->PostbackUrl_v2 = $this->createP2pForm->postbackurl_v2;

        $paySchet->sms_accept = 1;
        $paySchet->FIO = $this->createP2pForm->fullname;
        $paySchet->Dogovor = $this->createP2pForm->document_id;

        if (!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }
        return $paySchet;
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    protected function getUslugatovar()
    {
        return $this->createP2pForm->partner
            ->getUslugatovars()
            ->where([
                'IsCustom' => UslugatovarType::P2P,
                'IsDeleted' => 0,
            ])
            ->one();
    }


}
