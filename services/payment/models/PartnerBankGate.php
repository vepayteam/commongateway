<?php

namespace app\services\payment\models;

use app\models\bank\Banks;
use app\models\payonline\Partner;
use Yii;

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
 * @property Banks $bank
 * @property Partner $partner
 * @property UslugatovarType $uslugatovarType
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['PartnerId', 'BankId'], 'required'],
            [['PartnerId', 'BankId', 'Priority', 'Enable', 'TU', 'SchetType', 'CurrencyId'], 'integer'],
            [['Login', 'AdvParam_1', 'AdvParam_2', 'AdvParam_3', 'AdvParam_4', 'SchetNumber'], 'string', 'max' => 400],
            [['Token', 'Password'], 'safe'],
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
}
