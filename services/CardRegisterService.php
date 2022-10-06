<?php

namespace app\services;

use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\cardRegisterService\CreatePayschetData;
use app\services\cards\models\PanToken;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
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

    public function getOrCreateCard(PanToken $panToken, PartnerBankGate $partnerBankGate)
    {
        $partner = $partnerBankGate->partner;

        if ($partnerBankGate->TU === UslugatovarType::TOCARD) {
            $bankId = 0;
            $cardType = Cards::TYPE_CARD_OUT;
            $status = Cards::STATUS_ACTIVE;
        } else {
            $bankId = $partnerBankGate->BankId;
            $cardType = Cards::TYPE_CARD_IN;
            $status = Cards::STATUS_UNCONFIRMED;
        }

        $card = Cards::find()
            ->alias('cardAlias')
            ->notSoftDeleted()
            ->joinWith([
                /** @see Cards::$user */
                'user userAlias',
            ])
            ->andWhere([
                'cardAlias.IdPan' => $panToken->ID,
                'cardAlias.IdBank' => $bankId,
                'cardAlias.TypeCard' => $cardType,
                'userAlias.ExtOrg' => $partner->ID,
            ])
            ->orderBy(['cardAlias.ID' => SORT_DESC])
            ->limit(1) // optimization
            ->one();
        if ($card === null) {
            $card = new Cards();
            $card->CardNumber = $card->NameCard = $panToken->FirstSixDigits . '******' . $panToken->LastFourDigits;
            $card->ExtCardIDP = 0;
            $card->SrokKard = (int)($panToken->ExpDateMonth . $panToken->ExpDateYear);
            if ($panToken->CardHolder) {
                $card->CardHolder = mb_substr($panToken->CardHolder, 0, 99);
            }
            $card->Status = $status;
            $card->TypeCard = $cardType;
            $card->IdPan = $panToken->ID;
            $card->IdBank = $bankId;
            $card->save(false);
            $card->loadDefaultValues();
        }

        return $card;
    }
}