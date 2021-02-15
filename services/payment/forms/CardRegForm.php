<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use app\models\traits\ValidateFormTrait;
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

    /** @var Partner */
    public $partner;

    public function rules()
    {
        return [
            ['type', 'validateType'],
            ['partner', 'required'],
            [['extid'], 'string', 'max' => 40],
            [['successurl', 'failurl', 'cancelurl', 'postbackurl', 'postbackurl_v2'], 'url'],
            [['successurl', 'failurl', 'cancelurl', 'postbackurl', 'postbackurl_v2'], 'string', 'max' => 300],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59],
        ];
    }

    public function validateType()
    {
        if(!in_array($this->type, self::CARD_REG_TYPES)) {
            $this->addError('type', 'Тип регистрации не корректный');
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
