<?php

namespace app\services;

use app\models\api\Reguser;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\cardRegisterService\CreatePayschetData;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\PaySchet;
use yii\base\Component;

class CardRegisterService extends Component
{
    private const PAYMENT_AMOUNT_PAYMENT = 1100;
    private const PAYMENT_AMOUNT_OUT = 0;

    /**
     * @var LanguageService
     */
    private $languageService;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->languageService = \Yii::$app->get(LanguageService::class);
    }

    /**
     * Creates an invoice ({@see PaySchet}) to pay to register a card.
     *
     * @throws GateException
     * @see MfoCardRegStrategy
     */
    public function createPayschet(Partner $partner, CreatePayschetData $data): PaySchet
    {
        /** @todo Analyze, then remove/refactor the linking to user. */
        $extUser = $partner->ID . '-' . time() . random_int(100, 999);
        $reguser = new Reguser();
        $user = $reguser->findUser(
            '0',
            $extUser,
            md5($partner->ID . '-' . time()),
            $partner->ID,
            false
        );

        // build the adapter
        $uslugatovar = Uslugatovar::findOne(['ID' => Uslugatovar::REG_CARD_ID]);
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->build($partner, $uslugatovar);

        // create and save PaySchet
        $paySchet = new PaySchet();
        switch ($data->getType()) {
            case CreatePayschetData::TYPE_OUT:
                $paySchet->Bank = 0;
                $paySchet->SummPay = self::PAYMENT_AMOUNT_OUT;
                break;
            case CreatePayschetData::TYPE_PAYMENT:
                $paySchet->Bank = $bankAdapterBuilder->getBankAdapter()->getBankId();
                $paySchet->SummPay = self::PAYMENT_AMOUNT_PAYMENT;
                break;
            default:
                throw new \LogicException('Unknown type.');
        }
        $paySchet->IdUser = $user->ID;
        $paySchet->IdUsluga = $bankAdapterBuilder->getUslugatovar()->ID;
        $paySchet->IdOrg = $partner->ID;
        $paySchet->Extid = $data->getExtId();
        $paySchet->QrParams = '';
        $paySchet->UserUrlInform = $bankAdapterBuilder->getUslugatovar()->UrlInform;
        $paySchet->SuccessUrl = $data->getSuccessUrl();
        $paySchet->FailedUrl = $data->getFailUrl();
        $paySchet->CancelUrl = $data->getCancelUrl();
        $paySchet->PostbackUrl = $data->getPostbackUrl();
        $paySchet->PostbackUrl_v2 = $data->getPostbackUrlV2();
        $paySchet->sms_accept = 1;
        $paySchet->save(false);
        $paySchet->loadDefaultValues();

        $this->languageService->saveApiLanguage($paySchet->ID, $data->getLanguage());

        return $paySchet;
    }
}