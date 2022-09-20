<?php

namespace app\modules\mfo\models;

use app\components\validators\LuhnValidator;
use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\services\LanguageService;
use app\services\payment\models\Currency;
use app\services\payment\models\PaySchet;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\validators\Validator;

class PayToCardForm extends Model
{
    /** @var Partner */
    private $partner;
    /** @var Cards */
    private $presetSenderCard;

    /**
     * @var string|int
     */
    public $amount;
    /**
     * @var string One of currency codes, eg. 'RUB', 'USD'.  See {@see Currency::$Code}.
     */
    public $currency;
    /**
     * @var string|null Agreement ID.
     */
    public $documentId;
    /**
     * @var string Client full name. Max length: 80.
     */
    public $fullName;
    /**
     * @var string
     */
    public $extId;
    /**
     * @var int Payment timeout in minutes. Min: 10, max: 59. Default 30.
     */
    public $timeout;
    /**
     * @var string
     */
    public $successUrl;
    /**
     * @var string
     */
    public $failUrl;
    /**
     * @var string
     */
    public $cancelUrl;
    /**
     * @var string
     */
    public $postbackUrl;
    /**
     * @var string
     */
    public $postbackUrlV2;
    /**
     * @var string Language of payment form. See {@see LanguageService::ALL_API_LANG_LIST}.
     */
    public $language;
    /**
     * @var int Card registration. Default 0.
     */
    public $cardRegistration;
    /**
     * @var string|int Optional preset client's (payer's) card ID.
     */
    public $presetSenderCardId;
    /**
     * @var string Number of the card to transfer to.
     */
    public $recipientCardNumber;
    /**
     * @var string
     */
    public $description;

    public function __construct(Partner $partner)
    {
        parent::__construct();

        $this->partner = $partner;
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [['timeout'], 'default', 'value' => 30],
            [['cardRegistration'], 'default', 'value' => 0],

            [['amount', 'recipientCardNumber'], 'required'],

            [['amount'], 'number', 'min' => 1, 'max' => 1000000],
            [['recipientCardNumber'], 'match', 'pattern' => '/^\d{16}|\d{18}$/'],
            [['recipientCardNumber'], LuhnValidator::class],
            [
                ['recipientCardNumber'], 'in',
                'range' => \Yii::$app->params['testCards'],
                'when' => function () {
                    return \Yii::$app->params['TESTMODE'] === 'Y';
                }
            ],
            [['documentId', 'extId'], 'string', 'max' => 40],
            [['fullName'], 'string', 'max' => 80],
            [['cardRegistration'], 'in', 'range' => [1, 0], 'strict' => true],
            [['currency'], 'exist', 'targetClass' => Currency::class, 'targetAttribute' => 'Code'],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59],
            [['description'], 'string', 'max' => 200],

            [['presetSenderCardId'], 'validatePresetSenderCardId'], /** {@see validatePresetSenderCardId()} */

            [['successUrl', 'failUrl', 'cancelUrl'], 'string', 'max' => 1000],
            [['successUrl', 'failUrl', 'cancelUrl'], 'url'],

            [['postbackUrl', 'postbackUrlV2'], 'string', 'max' => 255],
            [['postbackUrl', 'postbackUrlV2'], 'url'],

            [['language'], 'in', 'range' => LanguageService::ALL_API_LANG_LIST],

            [
                ['extId'], 'unique',
                'targetClass' => PaySchet::class, 'targetAttribute' => 'ExtId',
                'filter' => function (ActiveQuery $query) {
                    $query->andWhere(['IdOrg' => $this->partner->ID]);
                }
            ],
        ];
    }

    public function validatePresetSenderCardId()
    {
        if (empty($this->presetSenderCardId)) {
            return;
        }

        $card = Cards::findOne(['ID' => $this->presetSenderCardId]);
        if ($card === null || $card->user->ExtOrg !== $this->partner->ID) {
            $this->addError('presetSenderCardId', 'Карты не существует.');
            return;
        }
        $this->presetSenderCard = $card;

        $panToken = $this->presetSenderCard->panToken;
        if ($panToken === null) {
            $this->addError('presetSenderCardId', 'Отсутствует Pan Token.');
            return;
        }
        if (empty($panToken->EncryptedPAN)) {
            $this->addError('presetSenderCardId', 'Карта просрочена.');
            return;
        }
        $cardNumber = (new CardToken())->GetCardByToken($panToken->ID);
        if (empty($cardNumber)) {
            $this->addError('presetSenderCardId', 'Пустая карта.');
        }
    }

    public function getPresetSenderCard(): ?Cards
    {
        return $this->presetSenderCard;
    }

    /**
     * {@inheritDoc}
     */
    public function createValidators(): \ArrayObject
    {
        /** @var Validator $validator */

        $validators = parent::createValidators();
        foreach ($validators as $validator) {
            $validator->isEmpty = function ($value) {
                return $value === null;
            };
        }
        return $validators;
    }
}