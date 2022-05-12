<?php

namespace app\services\payment\forms\monetix\models;

use app\models\traits\ValidateFormTrait;
use app\services\payment\forms\monetix\BaseModel;
use JsonSerializable;
use yii\base\Model;

class CardModel extends BaseModel
{
    const SCENARIO_IN = 'in';
    const SCENARIO_OUT = 'out';

    /** @var string */
    public $pan;
    /** @var int */
    public $year;
    /** @var int */
    public $month;
    /** @var string */
    public $card_holder;
    /** @var string */
    public $cvv;
    /** @var string */
    public $security_code;
    /** @var bool */
    public $save;
    /** @var int */
    public $stored_card_type;

    public function rules()
    {
        return [
            [['pan', 'year', 'month', 'cvv', 'card_holder'], 'required', 'on' => [self::SCENARIO_IN]],
            [['pan'], 'required', 'on' => [self::SCENARIO_OUT]],
            [['year', 'month'], 'number'],
            [['pan', 'cvv', 'card_holder'], 'string'],
        ];
    }
}