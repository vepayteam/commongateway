<?php

namespace app\services\payment\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "banks".
 *
 * @property int $ID
 * @property string $Name
 * @property string $ChannelName
 * @property float $JkhComis
 * @property float $JkhComisMin
 * @property float $EcomComis
 * @property float $EcomComisMin
 * @property float $AFTComis
 * @property float $AFTComisMin
 * @property float $OCTComis
 * @property float $OCTComisMin
 * @property float $OCTVozn
 * @property float $OCTVoznMin
 * @property float $FreepayComis
 * @property float $FreepayComisMin
 * @property float $FreepayVozn
 * @property float $FreepayVoznMin
 * @property float|null $VyvodBankComis
 * @property int $LastWorkIn
 * @property int $LastInPay
 * @property int $LastInCheck
 * @property int $UsePayIn
 * @property int $UseApplePay
 * @property int $UseGooglePay
 * @property int $UseSamsungPay
 * @property \yii\db\ActiveQuery|PaySchet[] $paySchets
 * @property string $name
 * @property int $SortOrder
 */
class Bank extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ID', 'Name', 'ChannelName'], 'required'],
            [['ID', 'LastWorkIn', 'LastInPay', 'LastInCheck', 'UsePayIn', 'UseApplePay', 'UseGooglePay', 'UseSamsungPay', 'SortOrder'], 'integer'],
            [['JkhComis', 'JkhComisMin', 'EcomComis', 'EcomComisMin', 'AFTComis', 'AFTComisMin', 'OCTComis', 'OCTComisMin', 'OCTVozn', 'OCTVoznMin', 'FreepayComis', 'FreepayComisMin', 'FreepayVozn', 'FreepayVoznMin', 'VyvodBankComis'], 'number'],
            [['Name', 'ChannelName'], 'string', 'max' => 250],
            [['ID'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Name' => 'Name',
            'ChannelName' => 'ChannelName',
            'JkhComis' => 'Jkh Comis',
            'JkhComisMin' => 'Jkh Comis Min',
            'EcomComis' => 'Ecom Comis',
            'EcomComisMin' => 'Ecom Comis Min',
            'AFTComis' => 'Aft Comis',
            'AFTComisMin' => 'Aft Comis Min',
            'OCTComis' => 'Oct Comis',
            'OCTComisMin' => 'Oct Comis Min',
            'OCTVozn' => 'Oct Vozn',
            'OCTVoznMin' => 'Oct Vozn Min',
            'FreepayComis' => 'Freepay Comis',
            'FreepayComisMin' => 'Freepay Comis Min',
            'FreepayVozn' => 'Freepay Vozn',
            'FreepayVoznMin' => 'Freepay Vozn Min',
            'VyvodBankComis' => 'Vyvod Bank Comis',
            'LastWorkIn' => 'Last Work In',
            'LastInPay' => 'Last In Pay',
            'LastInCheck' => 'Last In Check',
            'UsePayIn' => 'Use Pay In',
            'UseApplePay' => 'Use Apple Pay',
            'UseGooglePay' => 'Use Google Pay',
            'UseSamsungPay' => 'Use Samsung Pay',
            'SortOrder' => 'Sort Order',
        ];
    }

    public function getName(): string
    {
        return $this->Name;
    }

    /**
     * Gets query for [[PaySchets]].
     *
     * @return \yii\db\ActiveQuery|PaySchet[]
     */
    public function getPaySchets()
    {
        return $this->hasMany(PaySchet::className(), ['bank' => 'id']);
    }
}