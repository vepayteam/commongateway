<?php


namespace app\services\payment\forms;


use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\traits\ValidateFormTrait;
use app\services\cardRegisterService\CreatePayschetData;
use app\services\LanguageService;
use yii\base\Model;

class CardRegForm extends Model implements CreatePayschetData
{
    use ValidateFormTrait;

    const CARD_REG_TYPE_BY_PAY = 0;
    const CARD_REG_TYPE_BY_OUT = 1;

    const CARD_REG_TYPES = [
        self::CARD_REG_TYPE_BY_PAY,
        self::CARD_REG_TYPE_BY_OUT,
    ];

    const MUTEX_TIMEOUT = 20;

    public $type;
    public $extid = '';
    public $timeout = 15;
    public $successurl = '';
    public $failurl = '';
    public $cancelurl = '';
    public $postbackurl = '';
    public $postbackurl_v2 = '';
    public $language;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string
     */
    public $card;

    public function rules()
    {
        return [
            [['type'], 'required'],
            [['type'], 'in', 'range' => self::CARD_REG_TYPES],
            [['extid'], 'string', 'max' => 40],
            [['successurl', 'failurl', 'cancelurl', 'postbackurl', 'postbackurl_v2'], 'url'],
            [['successurl', 'failurl', 'cancelurl'], 'string', 'max' => 1000],
            [['postbackurl', 'postbackurl_v2'], 'string', 'max' => 255],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59],
            [['card'], 'match', 'pattern' => '/^\d{16}|\d{18}$/'],
            [['card'], 'validateCard'], /** @see validateCard() */
            [['language'], 'in', 'range' => LanguageService::ALL_API_LANG_LIST],
            [['email'], 'email'],
        ];
    }

    public function validateCard()
    {
        if ($this->hasErrors('card')) {
            return;
        }

        // На тестовом контуре проверяем является ли карта тестовой.
        if (\Yii::$app->params['TESTMODE'] === 'Y' && !in_array($this->card, \Yii::$app->params['testCards'])) {
            $this->addError('card', 'На тестовом контуре допускается использовать только тестовые карты');
        }

        // Валидация по алгоритму Луна.
        if (!Cards::CheckValidCard($this->card)) {
            $this->addError('card', \Yii::t('app.payment-errors', 'Неверный номер карты'));
        }
    }

    /**
     * @return string
     */
    public function getMutexKey(): string
    {
        return 'getPaySchetExt' . $this->extid;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getExtId(): ?string
    {
        return $this->extid;
    }

    public function getSuccessUrl(): ?string
    {
        return $this->successurl;
    }

    public function getFailUrl(): ?string
    {
        return $this->failurl;
    }

    public function getCancelUrl(): ?string
    {
        return $this->cancelurl;
    }

    public function getPostbackUrl(): ?string
    {
        return $this->postbackurl;
    }

    public function getPostbackUrlV2(): ?string
    {
        return $this->postbackurl_v2;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }
}
