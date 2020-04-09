<?php

namespace app\models\bank;

use Yii;

/**
 * This is the model class for table "banks".
 *
 * @property int $ID
 * @property string $Name
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
 * @property float $VyvodBankComis
 */
class Banks extends \yii\db\ActiveRecord
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
            [['ID', 'Name'], 'required'],
            [['ID'], 'integer'],
            [['JkhComis', 'JkhComisMin', 'EcomComis', 'EcomComisMin', 'AFTComis', 'AFTComisMin', 'OCTComis', 'OCTComisMin',
                'OCTVozn', 'OCTVoznMin', 'FreepayComis', 'FreepayComisMin', 'FreepayVozn', 'FreepayVoznMin', 'VyvodBankComis'
            ], 'number'],
            [['Name'], 'string', 'max' => 250],
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
            'JkhComis' => 'ЖКХ %',
            'JkhComisMin' => 'ЖКХ, не менее руб',
            'EcomComis' => 'Ecom %',
            'EcomComisMin' => 'Ecom, не менее руб',
            'AFTComis' => 'AFT %',
            'AFTComisMin' => 'AFT, не менее руб',
            'OCTComis' => 'OCT %',
            'OCTComisMin' => 'OCT, не менее руб',
            'OCTVozn' => 'OCT вознаграждение %',
            'OCTVoznMin' => 'OCT, не менее руб',
            'FreepayComis' => 'Freepay %',
            'FreepayComisMin' => 'Freepay, не менее руб',
            'FreepayVozn' => 'Freepay вознаграждение %',
            'FreepayVoznMin' => 'Freepay, не менее руб',
            'VyvodBankComis' => 'Сумма за вывод средтсв, руб'
        ];
    }
}
