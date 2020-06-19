<?php

namespace app\models\bank;

use app\models\Options;
use app\models\payonline\Partner;
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
 * @property int $LastWorkIn
 * @property int $LastInPay
 * @property int $LastInCheck
 * @property int $UsePayIn
 * @property int $UseApplePay
 * @property int $UseGooglePay
 * @property int $UseSamsungPay
 * @property int $SortOrder
 */
class Banks extends \yii\db\ActiveRecord
{
    const DEFAULT_BANK_CLASS = TCBank::class;
    const BANK_CLASSES = [
        2 => TCBank::class,
        3 => MTSBank::class,
    ];
    const BANK_BY_PAYMENT_OPTION_NAME = 'bank_payment_id';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banks';
    }

    /**
     * @param Partner|null $partner
     * @return string
     */
    public static function getBankClassByPayment(Partner $partner = null)
    {
        $id = null;
        $option = Options::findOne(['Name' => self::BANK_BY_PAYMENT_OPTION_NAME]);
        if(is_null($partner) || !$partner->BankForPaymentId || $option->Value != 0) {
            $id = $option->Value;
        } else {
            $id = $partner->BankForPaymentId;
        }

        $bankClassIds = array_keys(self::BANK_CLASSES);
        if(in_array($id, $bankClassIds)) {
            return self::BANK_CLASSES[$id];
        } else {
            return self::DEFAULT_BANK_CLASS;
        }
    }

    /**
     * @return array
     */
    public static function getBanksByDropdown()
    {
        $result = [];
        /** @var Banks $bank */
        foreach(Banks::find()->select('ID, Name')->all() as $bank) {
            $result[$bank->ID] = $bank->Name;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ID', 'Name'], 'required'],
            [['ID', 'LastWorkIn', 'LastInPay', 'LastInCheck', 'UsePayIn', 'UseApplePay', 'UseGooglePay', 'UseSamsungPay', 'SortOrder'], 'integer'],
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
            'VyvodBankComis' => 'Сумма за вывод средтсв, руб',
        ];
    }
}
