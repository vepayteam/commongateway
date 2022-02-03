<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use app\services\ident\traits\ErrorModelTrait;
use app\services\payment\models\PaySchet;
use yii\base\DynamicModel;
use yii\base\Model;
use yii\helpers\ArrayHelper;

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
    public $client;
    public $name;
    public $account;
    public $phone;
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
            [['amount'], 'number', 'min' => 1, 'max' => 21000000, 'on' => [self::SCENARIO_UL, self::SCENARIO_FL]],
            [['extid'], 'string', 'max' => 40],
            [['name', 'inn', 'account', 'bic', 'descript', 'amount'], 'required', 'on' => [self::SCENARIO_UL]],
            [['inn', 'account', 'bic', 'descript', 'amount'], 'required', 'on' => self::SCENARIO_FL],
            [['account', 'bic', 'amount'], 'required', 'on' => self::SCENARIO_BRS_CHECK],
            [['sms'], 'integer', 'on' => [self::SCENARIO_UL, self::SCENARIO_FL]],

            ['fio', 'required', 'on' => [self::SCENARIO_FL]],
            ['fio', 'string', 'max' => 150, 'on' => [self::SCENARIO_FL]],

            ['phone', 'match', 'pattern' => '/^7\d{10}$/', 'message' => 'Неверный номер телефона'],
            ['client', 'validateClient'],

            ['amount', 'filter', 'filter' => function ($value) {
                return $value * 100;
            }],
        ];
    }

    public function validateClient(): void
    {
        if (!is_array($this->client)) {
            $this->addError('client', 'Значение "client" должно быть массивом.');
        }
        $attributes = ArrayHelper::merge([
            'firstName' => null,
            'middleName' => null,
            'lastName' => null,
        ], $this->client);
        $model = DynamicModel::validateData($attributes, [
            [['firstName', 'middleName'], 'string', 'max' => 32],
            [['lastName'], 'string', 'max' => 100],
        ]);
        foreach ($model->getFirstErrors() as $error) {
            $this->addError('client', $error);
        }
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->client['lastName'] ?? null;
    }

    /**
     * @return mixed|string
     */
    public function getFirstName(): ?string
    {
        return $this->client['firstName'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->client['middleName'] ?? null;
    }

    /**
     * @return string
     */
    public function getPhoneToSend(): string
    {
        return '00'.$this->phone;
    }
}
