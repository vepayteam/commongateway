<?php


namespace app\services\payment\payment_strategies\merchant;


use app\models\payonline\Uslugatovar;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\IBankAdapter;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\MerchantPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\models\repositories\CurrencyRepository;
use app\services\payment\models\UslugatovarType;
use app\services\payment\PaymentService;
use Yii;
use yii\base\DynamicModel;

class MerchantPayCreateStrategy
{
    protected $payForm;
    /** @var CurrencyRepository */
    protected $currencyRepository;

    public function __construct(MerchantPayForm $merchantPayForm)
    {
        $this->payForm = $merchantPayForm;
        $this->currencyRepository = new CurrencyRepository();
    }

    /**
     * @return PaySchet
     * @throws CreatePayException
     * @throws GateException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function exec(): PaySchet
    {
        /** @var Uslugatovar $uslugatovar */
        $uslugatovar = $this->getUslugatovar();
        $currency = $this->currencyRepository->getCurrency($this->payForm->currency)[0];
        Yii::warning('getUslugatovar extid=' . $this->payForm->extid, 'merchant');
        if (!$uslugatovar) {
            throw new GateException('Услуга не найдена');
        }

        $validateErrors = $this->getPaymentService()->validatePaySchetWithUslugatovar($this->payForm, $uslugatovar);
        if (count($validateErrors) > 0) {
            throw new GateException($validateErrors[0]);
        }

        Yii::warning('BankAdapterBuilder extid=' . $this->payForm->extid, 'merchant');
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($this->payForm->partner, $uslugatovar, $currency);

        Yii::warning('getReplyRequest extid=' . $this->payForm->extid, 'merchant');
        $replyPaySchet = $this->getReplyRequest($bankAdapterBuilder);

        if ($replyPaySchet && $replyPaySchet->SummPay == $this->payForm->amount) {
            return $replyPaySchet;
        } elseif ($replyPaySchet && $replyPaySchet->SummPay != $this->payForm->amount) {
            throw new CreatePayException('Нарушение уникальности запроса');
        }
        Yii::warning('createPaySchet extid=' . $this->payForm->extid, 'merchant');
        $paySchet = $this->createPaySchet($bankAdapterBuilder);
        return $paySchet;
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    protected function getUslugatovar()
    {
        return $this->payForm->partner
            ->getUslugatovars()
            ->where([
                'IsCustom' => $this->payForm->type == 1 ? UslugatovarType::JKH : UslugatovarType::ECOM,
                'IsDeleted' => 0,
            ])
            ->one();
    }

    protected function getReplyRequest(BankAdapterBuilder $bankAdapterBuilder)
    {
        $paySchet = PaySchet::findOne([
            'IdOrg' => $this->payForm->partner->ID,
            'IdUsluga' => $bankAdapterBuilder->getUslugatovar()->ID,
            'Extid' => $this->payForm->extid,
        ]);

        return $paySchet;
    }

    /**
     * @param BankAdapterBuilder $bankAdapterBuilder
     *
     * @return PaySchet
     * @throws CreatePayException
     */
    protected function createPaySchet(BankAdapterBuilder $bankAdapterBuilder): PaySchet
    {
        $paySchet = new PaySchet();
        $currencyRepository = new CurrencyRepository();
        $currency = $currencyRepository->getCurrency($this->payForm->currency);

        $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
        $paySchet->IdUsluga = $bankAdapterBuilder->getUslugatovar()->ID;
        $paySchet->IdOrg = $this->payForm->partner->ID;
        $paySchet->Extid = $this->payForm->extid;
        $paySchet->QrParams = $this->payForm->descript;
        $paySchet->SummPay = $this->payForm->amount;
        $paySchet->CurrencyId = $currency->Id;
        $paySchet->DateCreate = time();
        $paySchet->DateLastUpdate = time();
        $paySchet->IsAutoPay = 0;
        $paySchet->UserUrlInform = $bankAdapterBuilder->getUslugatovar()->UrlInform;

        $paySchet->SuccessUrl = $this->payForm->successurl;
        $paySchet->FailedUrl = $this->payForm->failurl;
        $paySchet->CancelUrl = $this->payForm->cancelurl;
        $paySchet->PostbackUrl = $this->payForm->postbackurl;
        $paySchet->PostbackUrl_v2 = $this->payForm->postbackurl_v2;

        $paySchet->sms_accept = 1;
        $paySchet->FIO = $this->payForm->fullname;
        $paySchet->Dogovor = $this->payForm->document_id;
        //TODO: client personal data save to related db table & refactor
        if ($this->payForm->client) {
            if (isset($this->payForm->client['email']) && $this->payForm->client['email']) {
                $paySchet->UserEmail = $this->payForm->client['email'];
            }
            if (isset($this->payForm->client['address']) && $this->payForm->client['address']) {
                $paySchet->AddressUser = $this->payForm->client['address'];
            }
            if (isset($this->payForm->client['city']) && $this->payForm->client['city']) {
                $paySchet->CityUser = $this->payForm->client['city'];
            }
            if (isset($this->payForm->client['country']) && $this->payForm->client['country']) {
                $paySchet->CountryUser = $this->payForm->client['country'];
            }
            if (isset($this->payForm->client['phone']) && $this->payForm->client['phone']) {
                $paySchet->PhoneUser = $this->payForm->client['phone'];
            }
            if (isset($this->payForm->client['login']) && $this->payForm->client['login']) {
                $paySchet->LoginUser = $this->payForm->client['login'];
            }
            if (isset($this->payForm->client['zip']) && $this->payForm->client['zip']) {
                $paySchet->ZipUser = $this->payForm->client['zip'];
            }
        }
        if (!$paySchet->save()) {
            throw new CreatePayException('Не удалось создать счет');
        }
        return $paySchet;
    }

    /**
     * @return PaymentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getPaymentService()
    {
        return Yii::$container->get('PaymentService');
    }

}
