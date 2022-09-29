<?php

namespace app\services\payment\models;

use app\models\bank\Banks;
use app\models\payonline\Partner;

/**
 * This is the model class for table "partner_bank_gates".
 *
 * @property int $Id
 * @property int $PartnerId
 * @property int $BankId
 * @property string|null $TU
 * @property string|null $SchetNumber
 * @property string|null $SchetType
 * @property string|null $Login
 * @property string|null $Token
 * @property string|null $Password
 * @property string|null $AdvParam_1
 * @property string|null $AdvParam_2
 * @property string|null $AdvParam_3
 * @property string|null $AdvParam_4
 * @property int $CurrencyId
 * @property int|null $Priority
 * @property int|null $Enable

 * @property int|bool $UseGateCompensation Использовать комиссию шлюза
 * @property int $FeeCurrencyId Валюта фиксированной комиссии
 * @property int $MinimalFeeCurrencyId Валюта минимальной комиссии
 * @property float $ClientCommission Процентная комиссия от клиента
 * @property float $ClientFee Фиксированная комиссия от клиента
 * @property float $ClientMinimalFee Минимальная комиссия от клиента
 * @property float $PartnerCommission Процентная комиссия от контрагента
 * @property float $PartnerFee Фиксированная комиссия от контрагента
 * @property float $PartnerMinimalFee Минимальная комиссия от контрагента
 * @property float $BankCommission Процентная комиссия банку
 * @property float $BankFee Фиксированная комиссия банку
 * @property float $BankMinimalFee Минимальная комиссия банку
 *
 * @property Banks $bank
 * @property-read Partner $partner {@see PartnerBankGate::getPartner()}
 * @property UslugatovarType $uslugatovarType
 * @property-read Currency $currency
 * @property-read Currency $feeCurrency
 * @property-read Currency $minimalFeeCurrency
 *
 */
class PartnerBankGate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'partner_bank_gates';
    }

    private function commissionValueFields()
    {
        return [
            'ClientCommission', 'ClientFee', 'ClientMinimalFee',
            'PartnerCommission', 'PartnerFee', 'PartnerMinimalFee',
            'BankCommission', 'BankFee', 'BankMinimalFee',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $commissionValueFields = $this->commissionValueFields();

        return [
            [['PartnerId', 'BankId'], 'required'],
            [['PartnerId', 'BankId', 'Priority', 'Enable', 'TU', 'SchetType', 'CurrencyId'], 'integer'],
            [['Login', 'AdvParam_1', 'AdvParam_2', 'AdvParam_3', 'AdvParam_4', 'SchetNumber'], 'string', 'max' => 400],
            [['Token', 'Password'], 'safe'],
            [['UseGateCompensation'], 'in', 'range' => [0, 1]],
            [
                ['FeeCurrencyId', 'MinimalFeeCurrencyId'],
                'exist', 'targetClass' => Currency::class, 'targetAttribute' => 'Id',
            ],
            [
                ['FeeCurrencyId', 'MinimalFeeCurrencyId'],
                'required',
                'when' => function (PartnerBankGate $model) {
                    return (bool)$model->UseGateCompensation;
                },
            ],
            [$commissionValueFields, 'default', 'value' => null],
            [$commissionValueFields, 'double', 'min' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'PartnerId' => 'Partner ID',
            'BankId' => 'Bank ID',
            'TU' => 'Tu',
            'Login' => 'Login',
            'Token' => 'Token',
            'Password' => 'Password',
            'AdvParam_1' => 'Adv Param 1',
            'AdvParam_2' => 'Adv Param 2',
            'AdvParam_3' => 'Adv Param 3',
            'AdvParam_4' => 'Adv Param 4',
            'Priority' => 'Priority',
            'Enable' => 'Enable',
            'SchetType' => 'SchetType',
            'SchetNumber' => 'SchetNumber',
            'CurrencyId' => 'CurrencyId',
        ];
    }

    public function getPartner()
    {
        return $this->hasOne(Partner::class, ['ID' => 'PartnerId']);
    }

    public function getUslugatovarType()
    {
        return $this->hasOne(UslugatovarType::class, ['ID' => 'TU']);
    }

    public function getBank()
    {
        return $this->hasOne(Banks::class, ['ID' => 'BankId']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['ID' => 'CurrencyId']);
    }

    public function getFeeCurrency()
    {
        return $this->hasOne(Currency::class, ['ID' => 'FeeCurrencyId']);
    }

    public function getMinimalFeeCurrency()
    {
        return $this->hasOne(Currency::class, ['ID' => 'MinimalFeeCurrencyId']);
    }
}
