<?php

namespace app\models\payonline;

use yii\db\ActiveQuery;

/**
 * This is the model class for table "partner_bank_rekviz".
 *
 * @property string $ID
 * @property string $IdPartner
 * @property string $NamePoluchat
 * @property string $INNPolushat
 * @property string $KPPPoluchat
 * @property string $KorShetPolushat
 * @property string $RaschShetPolushat
 * @property string $NameBankPoluchat
 * @property string $SityBankPoluchat
 * @property string $BIKPoluchat
 * @property string $PokazKBK
 * @property string $OKATO
 * @property string $NaznachenPlatez
 * @property string $SummReestrAutoOplat
 * @property integer $MinSummReestrToOplat
 * @property integer $MaxIntervalOplat
 * @property integer $IsDecVoznagPerecisl
 * @property integer $ExportBankType
 * @property string $DateLastExport
 * @property integer $BalanceSumm
 * @property integer $IsDeleted
 *
 * @property-read Partner $partner {@see PartnerBankRekviz::getPartner()}
 */
class PartnerBankRekviz extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner_bank_rekviz';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['IdPartner', 'SummReestrAutoOplat', 'MinSummReestrToOplat', 'MaxIntervalOplat', 'IsDecVoznagPerecisl', 'ExportBankType'], 'integer'],
            [['SummReestrAutoOplat', 'MinSummReestrToOplat', 'MaxIntervalOplat', 'ExportBankType'], 'default', 'value' => 0],
            [['NamePoluchat'], 'string', 'max' => 200],
            [['INNPolushat', 'KPPPoluchat', 'BIKPoluchat', 'OKATO'], 'string', 'max' => 20],
            [['KorShetPolushat', 'RaschShetPolushat', 'PokazKBK'], 'string', 'max' => 40],
            [['NameBankPoluchat'], 'string', 'max' => 150],
            [['SityBankPoluchat'], 'string', 'max' => 80],
            [['NaznachenPlatez'], 'string', 'max' => 300],
            [['NamePoluchat', 'INNPolushat', 'BIKPoluchat', 'RaschShetPolushat', 'NaznachenPlatez'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            //'IdPartner' => 'id partner',
            'NamePoluchat' => 'Получатель (Юрлицо)',
            'INNPolushat' => 'ИНН получателя',
            'KPPPoluchat' => 'КПП получателя',
            'KorShetPolushat' => 'Кор.счет',
            'RaschShetPolushat' => 'Расчетный счет',
            'NameBankPoluchat' => 'Наименование банка',
            'SityBankPoluchat' => 'Город банка',
            'BIKPoluchat' => 'БИК',
            'PokazKBK' => 'КБК',
            'OKATO' => 'ОКТМО',
            'NaznachenPlatez' => 'Назначение платежа',
            'SummReestrAutoOplat' => 'сумма к перечислению (для предоплаты), 0 - долг оплатить',
            'MinSummReestrToOplat' => 'Cумма долга после которой оплату производить',
            'MaxIntervalOplat' => 'Максимальный срок между перечислениями в днях',
            'IsDecVoznagPerecisl' => 'удерживать вознаграждение при перечислении',
            'ExportBankType' => 'банк для выгрузки',
            //'DateLastExport' => 'дата последней оплаты',
            //'BalanceSumm' => 'сумма долга перед партнером',
            'IsDeleted' => 'Is Deleted',
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * @return ActiveQuery
     * @see Partner::getBankRekviz()
     */
    public function getPartner(): ActiveQuery
    {
        return $this->hasOne(Partner::class, ['ID' => 'IdPartner'])->inverseOf('bankRekviz');
    }
}
