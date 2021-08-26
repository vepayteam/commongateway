<?php

namespace app\models\payonline;

use app\models\payonline\active_query\PaySchetQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "pay_schet".
 *
 * @property int $ID
 * @property int $IdUser id user
 * @property int $IdKard id kard, esli privaizanoi kartoi oplata
 * @property int $IdUsluga id usluga
 * @property int $IdShablon id shablon
 * @property int $IdOrder id order_pay
 * @property int $IdOrg custom id partner
 * @property string|null $Extid custom partner vneshnii id
 * @property int $IdGroupOplat gruppovaya oplata po pay_schgroup
 * @property int $Period period reestra
 * @property string|null $Schetcheks pokazania schetchikov. razdelenie |
 * @property int $IdQrProv uslugatovar.ProfitIdProvider, esli bez shablona oplata
 * @property string|null $QrParams rekvizity dlia oplaty
 * @property int $SummPay summa plateja v kopeikah
 * @property int $ComissSumm summa komissii v kopeikah
 * @property int $MerchVozn komissia vepay, kop
 * @property int $BankComis komissia banka, kop
 * @property int $Status status: 0 - sozdan, 1 - oplachen, 2 - oshibka oplaty
 * @property string|null $ErrorInfo soobchenie oshibki
 * @property int $DateCreate date create
 * @property int $DateOplat date oplata
 * @property int $DateLastUpdate data poslednego obnovlenia zapisi
 * @property int $PayType tip oplaty: 0 - bankovskaya karta, 1 - qiwi, 2 - mail.ru
 * @property string|null $Operations
 * @property string|null $Version3DS
 * @property int|null $IsNeed3DSVerif
 * @property string|null $DsTransId
 * @property string|null $Eci
 * @property string|null $AuthValue3DS
 * @property string|null $CardRefId3DS
 * @property int $TimeElapsed srok oplaty v sec
 * @property string|null $ExtBillNumber nomer transakcii uniteller
 * @property int $ExtKeyAcces kod scheta uniteller
 * @property string|null $ApprovalCode kod avtorizacii
 * @property string|null $RRN nomer RRN
 * @property string|null $CardNum nomer karty
 * @property string|null $CardType tip karty
 * @property string|null $CardHolder derjatel karty
 * @property int $CardExp srok deistvia karty - MMYY
 * @property string|null $BankName bank karty
 * @property string|null $IPAddressUser ip adres platelshika
 * @property string|null $CountryUser strana platelshika
 * @property string|null $CityUser gorod platelshika
 * @property string|null $ZipUser
 * @property string|null $PhoneUser
 * @property string|null $LoginUser
 * @property string|null $AddressUser
 * @property int $UserClickPay 0 - ne klikal oplatu 1 - klikal oplatu
 * @property int $CountSendOK kollichestvo poslanyh zaprosov v magazin ob uspeshnoi oplate
 * @property int $SendKvitMail otpravit kvitanciuu ob oplate na pochtu
 * @property int $IdAgent id agent site
 * @property string|null $GisjkhGuid guid gis jkh
 * @property int $TypeWidget 0 - web communal 1 - mobile 2 - shop 3 - qr schet
 * @property int $Bank bank: 0 - rsb 1 - rossia
 * @property int $IsAutoPay 1 - avtoplatej
 * @property int $AutoPayIdGate
 * @property string|null $UrlFormPay url dlia perehoda k oplate
 * @property string|null $UserUrlInform url dlia kollbeka pletelshiky
 * @property string|null $UserKeyInform kluch dlia kollbeka pletelshiky
 * @property string|null $SuccessUrl url dlia vozvrata pri uspehe
 * @property string|null $FailedUrl url dlia vozvrata pri otkaze
 * @property string|null $CancelUrl
 * @property string|null $PostbackUrl
 * @property string|null $PostbackUrl_v2
 * @property int|null $sms_accept
 * @property string|null $Dogovor
 * @property string|null $FIO
 * @property string|null $UserEmail
 * @property string|null $RCCode
 * @property int $CurrencyId
 * @property Cards $cards
 * @property Uslugatovar $uslugatovar
 */
class PaySchet extends ActiveRecord
{
    
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'pay_schet';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'IdUser',
                    'IdKard',
                    'IdUsluga',
                    'IdShablon',
                    'IdOrder',
                    'IdOrg',
                    'IdGroupOplat',
                    'Period',
                    'IdQrProv',
                    'SummPay',
                    'ComissSumm',
                    'MerchVozn',
                    'BankComis',
                    'Status',
                    'DateCreate',
                    'DateOplat',
                    'DateLastUpdate',
                    'PayType',
                    'IsNeed3DSVerif',
                    'TimeElapsed',
                    'ExtKeyAcces',
                    'CardExp',
                    'UserClickPay',
                    'CountSendOK',
                    'SendKvitMail',
                    'IdAgent',
                    'TypeWidget',
                    'Bank',
                    'IsAutoPay',
                    'AutoPayIdGate',
                    'sms_accept',
                    'CurrencyId'
                ],
                'integer'
            ],
            [['Operations'], 'string'],
            [['Extid'], 'string', 'max' => 40],
            [['Schetcheks', 'CardHolder', 'BankName', 'CountryUser', 'CityUser'], 'string', 'max' => 100],
            [['QrParams'], 'string', 'max' => 500],
            [['ErrorInfo'], 'string', 'max' => 250],
            [
                [
                    'Version3DS',
                    'DsTransId',
                    'Eci',
                    'AuthValue3DS',
                    'CardRefId3DS',
                    'AddressUser',
                    'PostbackUrl',
                    'PostbackUrl_v2',
                    'Dogovor',
                    'FIO',
                    'UserEmail'
                ],
                'string',
                'max' => 255
            ],
            [['ExtBillNumber', 'GisjkhGuid', 'UserKeyInform'], 'string', 'max' => 50],
            [['ApprovalCode', 'RRN'], 'string', 'max' => 20],
            [['CardNum', 'CardType', 'IPAddressUser'], 'string', 'max' => 30],
            [['ZipUser'], 'string', 'max' => 16],
            [['PhoneUser', 'LoginUser'], 'string', 'max' => 32],
            [['UrlFormPay'], 'string', 'max' => 2000],
            [['UserUrlInform', 'SuccessUrl', 'FailedUrl', 'CancelUrl'], 'string', 'max' => 1000],
            [['RCCode'], 'string', 'max' => 10],
            [['idkard'], 'exist', 'skipOnError' => true, 'targetClass' => Cards::className(), 'targetAttribute' => ['idkard' => 'id']],
            [['iduser'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['iduser' => 'id']],
            [['idusluga'], 'exist', 'skipOnError' => true, 'targetClass' => Uslugatovar::className(), 'targetAttribute' => ['idusluga' => 'id']],
            [['idusluga'], 'exist', 'skipOnError' => true, 'targetClass' => Uslugatovar::className(), 'targetAttribute' => ['idusluga' => 'id']],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'ID' => 'ID',
            'IdUser' => 'id user',
            'IdKard' => 'id kard, esli privaizanoi kartoi oplata',
            'IdUsluga' => 'id usluga',
            'IdShablon' => 'id shablon',
            'IdOrder' => 'id order_pay',
            'IdOrg' => 'custom id partner',
            'Extid' => 'custom partner vneshnii id',
            'IdGroupOplat' => 'gruppovaya oplata po pay_schgroup',
            'Period' => 'period reestra',
            'Schetcheks' => 'pokazania schetchikov. razdelenie |',
            'IdQrProv' => 'uslugatovar.ProfitIdProvider, esli bez shablona oplata',
            'QrParams' => 'rekvizity dlia oplaty',
            'SummPay' => 'summa plateja v kopeikah',
            'ComissSumm' => 'summa komissii v kopeikah',
            'MerchVozn' => 'komissia vepay, kop',
            'BankComis' => 'komissia banka, kop',
            'Status' => 'status: 0 - sozdan, 1 - oplachen, 2 - oshibka oplaty',
            'ErrorInfo' => 'soobchenie oshibki',
            'DateCreate' => 'date create',
            'DateOplat' => 'date oplata',
            'DateLastUpdate' => 'data poslednego obnovlenia zapisi',
            'PayType' => 'tip oplaty: 0 - bankovskaya karta, 1 - qiwi, 2 - mail.ru',
            'Operations' => 'Operations',
            'Version3DS' => 'Version3 Ds',
            'IsNeed3DSVerif' => 'Is Need3 Ds Verif',
            'DsTransId' => 'Ds Trans ID',
            'Eci' => 'Eci',
            'AuthValue3DS' => 'Auth Value3 Ds',
            'CardRefId3DS' => 'Card Ref Id3 Ds',
            'TimeElapsed' => 'srok oplaty v sec',
            'ExtBillNumber' => 'nomer transakcii uniteller',
            'ExtKeyAcces' => 'kod scheta uniteller',
            'ApprovalCode' => 'kod avtorizacii',
            'RRN' => 'nomer RRN',
            'CardNum' => 'nomer karty',
            'CardType' => 'tip karty',
            'CardHolder' => 'derjatel karty',
            'CardExp' => 'srok deistvia karty - MMYY',
            'BankName' => 'bank karty',
            'IPAddressUser' => 'ip adres platelshika',
            'CountryUser' => 'strana platelshika',
            'CityUser' => 'gorod platelshika',
            'ZipUser' => 'Zip User',
            'PhoneUser' => 'Phone User',
            'LoginUser' => 'Login User',
            'AddressUser' => 'Address User',
            'UserClickPay' => '0 - ne klikal oplatu 1 - klikal oplatu',
            'CountSendOK' => 'kollichestvo poslanyh zaprosov v magazin ob uspeshnoi oplate',
            'SendKvitMail' => 'otpravit kvitanciuu ob oplate na pochtu',
            'IdAgent' => 'id agent site',
            'GisjkhGuid' => 'guid gis jkh',
            'TypeWidget' => '0 - web communal 1 - mobile 2 - shop 3 - qr schet',
            'Bank' => 'bank: 0 - rsb 1 - rossia',
            'IsAutoPay' => '1 - avtoplatej',
            'AutoPayIdGate' => 'Auto Pay Id Gate',
            'UrlFormPay' => 'url dlia perehoda k oplate',
            'UserUrlInform' => 'url dlia kollbeka pletelshiky',
            'UserKeyInform' => 'kluch dlia kollbeka pletelshiky',
            'SuccessUrl' => 'url dlia vozvrata pri uspehe',
            'FailedUrl' => 'url dlia vozvrata pri otkaze',
            'CancelUrl' => 'Cancel Url',
            'PostbackUrl' => 'Postback Url',
            'PostbackUrl_v2' => 'Postback Url V2',
            'sms_accept' => 'Sms Accept',
            'Dogovor' => 'Dogovor',
            'FIO' => 'Fio',
            'UserEmail' => 'User Email',
            'RCCode' => 'Rc Code',
            'CurrencyId' => 'Currency ID',
        ];
    }
    
    /**
     * {@inheritdoc}
     * @return PaySchetQuery the active query used by this AR class.
     */
    public static function find(): active_query\PaySchetQuery
    {
        return new PaySchetQuery(get_called_class());
    }
    
    /**
     * Gets query for [[Cards]].
     */
    public function getCards(): ActiveQuery
    {
        return $this->hasOne(Cards::className(), ['ID' => 'IdKard']);
    }
    
    /**
     * Gets query for [[user]].
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::className(), ['ID' => 'IdUser']);
    }
    
    /**
     * Gets query for [[Idusluga]].
     */
    public function getIdusluga(): ActiveQuery
    {
        return $this->hasOne(Uslugatovar::className(), ['ID' => 'IdUsluga']);
    }
    
    /**
     * Gets query for [[Idusluga0]].
     */
    public function getUslugatovar(): ActiveQuery
    {
        return $this->hasOne(Uslugatovar::className(), ['ID' => 'IdUsluga']);
    }
}
