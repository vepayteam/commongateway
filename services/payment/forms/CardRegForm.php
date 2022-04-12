<?php


namespace app\services\payment\forms;


use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\traits\ValidateFormTrait;
use app\services\LanguageService;
use yii\base\Model;

class CardRegForm extends Model
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
    public $language = LanguageService::API_LANG_RUS;

    /**
     * @var string
     */
    public $card;

    /** @var Partner */
    public $partner;

    public function rules()
    {
        return [
            ['type', 'validateType'],
            ['partner', 'required'],
            [['extid'], 'string', 'max' => 40],
            [['successurl', 'failurl', 'cancelurl', 'postbackurl', 'postbackurl_v2'], 'url'],
            [['successurl', 'failurl', 'cancelurl'], 'string', 'max' => 1000],
            [['postbackurl', 'postbackurl_v2'], 'string', 'max' => 255],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59],
            [['card'], 'match', 'pattern' => '/^\d{16}|\d{18}$/'],
            [['card'], 'validateCard'], /** @see validateCard() */
            [['language'], 'in', 'range' => LanguageService::ALL_API_LANG_LIST],
        ];
    }

    public function validateType()
    {
        if($this->type !== self::CARD_REG_TYPE_BY_PAY && $this->type !== self::CARD_REG_TYPE_BY_OUT) {
            $this->addError('type', 'Тип регистрации не корректный');
        }
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
            $this->addError('card', 'Неверный номер карты.');
        }
    }

    /**
     * @return string
     */
    public function getMutexKey()
    {
        return 'getPaySchetExt' . $this->extid;
    }
}
