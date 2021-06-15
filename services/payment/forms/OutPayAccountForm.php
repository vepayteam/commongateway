<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use app\services\ident\traits\ErrorModelTrait;
use app\services\payment\models\PaySchet;
use yii\base\Model;

class OutPayAccountForm extends Model
{
    const SCENARIO_UL = 'ul';
    const SCENARIO_FL = 'fl';
    const SCENARIO_BRS_CHECK = 'brs_check';

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
            [['fio', 'account', 'bic', 'amount'], 'required', 'on' => self::SCENARIO_BRS_CHECK],
            [['sms'], 'integer', 'on' => [self::SCENARIO_UL, self::SCENARIO_FL]],

            ['amount', 'filter', 'filter' => function ($value) {
                return $value * 100;
            }],
        ];
    }

    /**
     * @return mixed|string
     */
    public function getLastName()
    {
        return $this->getFioArray()[0];
    }

    /**
     * @return mixed|string
     */
    public function getFirstName()
    {
        return $this->getFioArray()[1];
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        if(count($this->getFioArray()) < 3) {
            return '';
        }
        return implode(' ', array_slice($this->getFioArray(), 2));
    }

    /**
     * @return false|string[]|null
     */
    private function getFioArray()
    {
        $result = explode(' ', $this->fio);
        if(count($result) < 2) {
            return null;
        } else {
            return $result;
        }
    }

}
