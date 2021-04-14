<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use app\services\ident\traits\ErrorModelTrait;
use app\services\payment\models\PaySchet;
use yii\base\Model;

class OutPayaccForm extends Model
{
    const SCENARIO_UL = 'ul';
    const SCENARIO_FL = 'fl';

    use ErrorModelTrait;

    /** @var PaySchet */
    public $paySchet;

    /** @var Partner */
    public $partner;
    public $extid;
    public $fio;
    public $name;
    public $account;
    public $bic;
    public $descript;
    public $amount;
    public $sms;

    public $inn = '';
    public $kpp = '';


    public function rules()
    {
        return [
            [['account'], 'match', 'pattern' => '/^\d{20}$/', 'on' => [self::SCENARIO_FL, self::SCENARIO_UL]],
            [['bic'], 'match', 'pattern' => '/^\d{9}$/', 'on' => [self::SCENARIO_FL, self::SCENARIO_UL]],
            [['descript'], 'string', 'max' => 210, 'on' => [self::SCENARIO_FL, self::SCENARIO_UL]],
            [['inn'], 'match', 'pattern' => '/^\d{10,13}$/', 'on' => [self::SCENARIO_FL, self::SCENARIO_UL]],
            [['kpp'], 'string', 'max' => 9, 'on' => [self::SCENARIO_UL]],
            [['name'], 'string', 'max' => 200, 'on' => [self::SCENARIO_UL]],
            [['fio'], 'string', 'max' => 150, 'on' => self::SCENARIO_FL],
            [['amount'], 'number', 'min' => 1, 'max' => 21000000, 'on' => [self::SCENARIO_UL, self::SCENARIO_FL]],
            [['extid'], 'string', 'max' => 40],
            [['name', 'inn', 'account', 'bic', 'descript', 'amount'], 'required', 'on' => [self::SCENARIO_UL]],
            [['fio', 'inn', 'account', 'bic', 'descript', 'amount'], 'required', 'on' => self::SCENARIO_FL],
            [['sms'], 'integer', 'on' => [self::SCENARIO_UL, self::SCENARIO_FL]],

            ['amount', 'filter', 'filter' => function ($value) {
                return $value * 100;
            }],
        ];
    }

}
