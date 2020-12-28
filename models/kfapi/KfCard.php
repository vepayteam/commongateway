<?php


namespace app\models\kfapi;

use app\models\payonline\User;
use app\services\card\base\CardBase;

class KfCard extends CardBase
{
    public const SCENARIO_INFO = "info";
    public const SCENARIO_REG = "reg";
    public const SCENARIO_GET = "get";

    public $id;
    public $card;
    public $type;

    public $extid = '';
    public $timeout = 15;
    public $successurl = '';
    public $failurl = '';
    public $cancelurl = '';

    /* @var null|User */
    public $user = null;

    public function rules()
    {
        return [
            [['card', 'id', 'type'], 'integer'],
            [['extid'], 'string', 'max' => 40, 'on' => [self::SCENARIO_REG]],
            [['successurl', 'failurl', 'cancelurl'], 'url', 'on' => [self::SCENARIO_REG]],
            [['successurl', 'failurl', 'cancelurl'], 'string', 'max' => 300, 'on' => [self::SCENARIO_REG]],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59, 'on' => [self::SCENARIO_REG]],
            [['card'], 'required', 'on' => self::SCENARIO_INFO],
            [['id'], 'required', 'on' => self::SCENARIO_GET],
            [['type'], 'integer', 'min' => 0, 'on' => self::SCENARIO_REG]
        ];
    }
}
