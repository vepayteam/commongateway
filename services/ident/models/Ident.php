<?php

namespace app\services\ident\models;

use app\models\payonline\Partner;
use app\models\traits\ValidateFormTrait;
use app\services\payment\banks\TKBankAdapter;
use app\services\payment\models\Bank;
use Yii;

/**
 * This is the model class for table "idents".
 *
 * @property int $Id
 * @property int $PartnerId
 * @property int $BankId
 * @property string|null $FirstName
 * @property string|null $LastName
 * @property string|null $Patronymic
 * @property string|null $Series
 * @property string|null $Number
 * @property string|null $Inn
 * @property string|null $Snils
 * @property string|null $BirthDay
 * @property string|null $IssueData
 * @property string|null $IssueCode
 * @property string|null $Issuer
 * @property int $DateCreated
 * @property int $DateUpdated
 * @property int|null $Status
 * @property string|null $Response
 * @property Partner $partner
 * @property Bank $bank
 */
class Ident extends \yii\db\ActiveRecord
{
    use ValidateFormTrait;

    const STATUS_CREATED = 0;
    const STATUS_WAITING = 1;
    const STATUS_ERROR = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_DENIED = 4;
    const STATUS_TIMEOUT = 5;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'idents';
    }

    /**
     * @param int $bankId
     */
    public function setBankScenario(int $bankId)
    {
        $bankIds = [TKBankAdapter::$bank];

        if(in_array($bankId, $bankIds)) {
            $this->scenario = $bankId;
        }
    }

    /**
     * @return string[]
     */
    public static function getTkbRequestParams()
    {
        return [
            'ExtId' => 'Id',
            'FirstName' => 'FirstName',
            'LastName' => 'LastName',
            'Patronymic' => 'Patronymic',
            'Series' => 'Series',
            'Number' => 'Number',
            'Inn' => 'Inn',
            'Snils' => 'Snils',
            'BirthDay' => 'BirthDay',
            'IssueDate' => 'IssueDate',
            'IssueCode' => 'IssueCode',
            'Issuer' => 'Issuer',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['PartnerId'], 'required'],
            [['PartnerId', 'DateCreated', 'DateUpdated', 'Status'], 'integer'],
            [['Response'], 'string'],
            [[
                'FirstName', 'LastName', 'Patronymic', 'Series', 'Number',
                'Inn', 'Snils', 'BirthDay', 'IssueData', 'IssueCode', 'Issuer'
            ], 'string', 'max' => 255],

            [['FirstName', 'LastName', 'Series', 'Number'], 'required', 'on' => [TKBankAdapter::$bank]],
            [['Inn', 'Snils'], 'validateInnOrSnils'],
            [['Patronymic'], 'validatePatronymicRequireIfSnilsNotEmpty', 'on' => [TKBankAdapter::$bank]],
        ];
    }

    public function validateInnOrSnils()
    {
        if(empty($this->Snils) && empty($this->Inn)) {
            $this->addError('Snils', 'ИНН или СНИЛС обязательны');
        }
    }

    public function validatePatronymicRequireIfSnilsNotEmpty()
    {
        if(!empty($this->Snils) && empty($this->Patronymic)) {
            $this->addError('Patronymic', 'Если передан СНИЛС, отчество обязательно');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'PartnerId' => 'Partner ID',
            'FirstName' => 'First Name',
            'LastName' => 'Last Name',
            'Patronymic' => 'Patronymic',
            'Series' => 'Series',
            'Number' => 'Number',
            'Inn' => 'Inn',
            'Snils' => 'Snils',
            'BirthDay' => 'Birth Day',
            'IssueData' => 'Issue Data',
            'IssueCode' => 'Issue Code',
            'Issuer' => 'Issuer',
            'DateCreated' => 'Date Created',
            'DateUpdated' => 'Date Updated',
            'Status' => 'Status',
            'Response' => 'Response',
        ];
    }

    public function getPartner()
    {
        return $this->hasOne(Partner::class, ['ID' => 'PartnerId']);
    }

    public function getBank()
    {
        return $this->hasOne(Bank::class, ['ID' => 'BankId']);
    }
}
