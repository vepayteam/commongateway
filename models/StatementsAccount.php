<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "statements_account".
 *
 * @property int $ID
 * @property int $IdPartner id partner
 * @property int $TypeAccount tip schet partnera - 0 - vydacha 1 - pogashenie 2 - nominalnyii
 * @property int|null $BnkId id
 * @property int $NumberPP number
 * @property int $DatePP data
 * @property int $SummPP summa
 * @property int $SummComis komissia vepay
 * @property string|null $Description naznachenie
 * @property int $IsCredit 0 - spisanie, 1 - popolnenie
 * @property string|null $Name kontragent
 * @property string|null $Inn inn
 * @property string|null $Account rsch.schet
 * @property string|null $Bic bik banka
 * @property string|null $Bank bank
 * @property string|null $BankAccount kor.schet
 * @property string|null $Kpp
 * @property int $DateRead data poluchenia ot tkb
 * @property int $DateDoc
 */
class StatementsAccount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'statements_account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['IdPartner', 'TypeAccount', 'BnkId', 'NumberPP', 'DatePP', 'SummPP', 'SummComis', 'IsCredit', 'DateRead', 'DateDoc'], 'integer'],
            [['Description'], 'string', 'max' => 500],
            [['Name', 'Bank'], 'string', 'max' => 250],
            [['Inn', 'Account', 'Bic', 'BankAccount', 'Kpp'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'ID' => 'ID',
            'IdPartner' => 'Id Partner',
            'TypeAccount' => 'Type Account',
            'BnkId' => 'Bnk ID',
            'NumberPP' => 'Number Pp',
            'DatePP' => 'Date Pp',
            'SummPP' => 'Summ Pp',
            'SummComis' => 'Summ Comis',
            'Description' => 'Description',
            'IsCredit' => 'Is Credit',
            'Name' => 'Name',
            'Inn' => 'Inn',
            'Account' => 'Account',
            'Bic' => 'Bic',
            'Bank' => 'Bank',
            'BankAccount' => 'Bank Account',
            'Kpp' => 'Kpp',
            'DateRead' => 'Date Read',
            'DateDoc' => 'Date Doc',
        ];
    }
}