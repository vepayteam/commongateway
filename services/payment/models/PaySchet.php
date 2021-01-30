<?php

namespace app\services\payment\models;

use app\models\payonline\Partner;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\services\notifications\models\NotificationPay;
use app\services\payment\exceptions\GateException;
use Yii;

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
 * @property Uslugatovar $uslugatovar
 * @property Partner $partner
 * @property PaySchetLog[] $log
 *
 * @property string $Version3DS
 * @property int $IsNeed3DSVerif
 * @property string $DsTransId
 * @property string $Eci
 * @property string $AuthValue3DS
 * @property string $CardRefId3DS
 */
class PaySchet extends \yii\db\ActiveRecord
{
    const STATUS_WAITING = 0;
    const STATUS_DONE = 1;
    const STATUS_ERROR = 2;
    const STATUS_CANCEL = 3;
    const STATUS_NOT_EXEC = 4;
    const STATUS_WAITING_CHECK_STATUS = 5;

    const STATUSES = [
        self::STATUS_WAITING => 'В обработке',
        self::STATUS_DONE => 'Оплачен',
        self::STATUS_ERROR => 'Отмена',
        self::STATUS_CANCEL => 'Возврат',
        self::STATUS_NOT_EXEC => 'Ожидается обработка',
        self::STATUS_WAITING_CHECK_STATUS => 'Ожидается запрос статуса',
    ];

    const STATUS_COLORS = [
        self::STATUS_WAITING => 'blue',
        self::STATUS_DONE => 'green',
        self::STATUS_ERROR => 'red',
        self::STATUS_CANCEL => '#FF3E00',
        self::STATUS_NOT_EXEC => 'blue',
        self::STATUS_WAITING_CHECK_STATUS => 'blue',
    ];

    const CHECK_3DS_CACHE_PREFIX = 'pay_schet__check-3ds-response';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pay_schet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdUser', 'IdKard', 'IdUsluga', 'IdShablon', 'IdOrder', 'IdOrg', 'IdGroupOplat', 'Period', 'IdQrProv',
                'SummPay', 'ComissSumm', 'MerchVozn', 'BankComis', 'Status', 'DateCreate', 'DateOplat', 'DateLastUpdate',
                'PayType', 'TimeElapsed', 'ExtKeyAcces', 'CardExp', 'UserClickPay', 'CountSendOK', 'SendKvitMail',
                'IdAgent', 'TypeWidget', 'Bank', 'IsAutoPay', 'AutoPayIdGate', 'sms_accept'
                ],
                'integer'
            ],
            [['Extid'], 'string', 'max' => 40],
            [['Schetcheks', 'CardHolder', 'BankName', 'CountryUser', 'CityUser'], 'string', 'max' => 100],
            [['QrParams'], 'string', 'max' => 500],
            [['ErrorInfo'], 'string', 'max' => 250],
            [['ExtBillNumber', 'GisjkhGuid', 'UserKeyInform'], 'string', 'max' => 50],
            [['ApprovalCode', 'RRN'], 'string', 'max' => 20],
            [['CardNum', 'CardType', 'IPAddressUser'], 'string', 'max' => 30],
            [['UrlFormPay'], 'string', 'max' => 2000],
            [['UserUrlInform', 'SuccessUrl', 'FailedUrl', 'CancelUrl'], 'string', 'max' => 1000],
            [['PostbackUrl', 'Dogovor', 'FIO', 'UserEmail'], 'string', 'max' => 255],
            [['RCCode'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IdUser' => 'Id User',
            'IdKard' => 'Id Kard',
            'IdUsluga' => 'Id Usluga',
            'IdShablon' => 'Id Shablon',
            'IdOrder' => 'Id Order',
            'IdOrg' => 'Id Org',
            'Extid' => 'Extid',
            'IdGroupOplat' => 'Id Group Oplat',
            'Period' => 'Period',
            'Schetcheks' => 'Schetcheks',
            'IdQrProv' => 'Id Qr Prov',
            'QrParams' => 'Qr Params',
            'SummPay' => 'Summ Pay',
            'ComissSumm' => 'Comiss Summ',
            'MerchVozn' => 'Merch Vozn',
            'BankComis' => 'Bank Comis',
            'Status' => 'Status',
            'ErrorInfo' => 'Error Info',
            'DateCreate' => 'Date Create',
            'DateOplat' => 'Date Oplat',
            'DateLastUpdate' => 'Date Last Update',
            'PayType' => 'Pay Type',
            'TimeElapsed' => 'Time Elapsed',
            'ExtBillNumber' => 'Ext Bill Number',
            'ExtKeyAcces' => 'Ext Key Acces',
            'ApprovalCode' => 'Approval Code',
            'RRN' => 'Rrn',
            'CardNum' => 'Card Num',
            'CardType' => 'Card Type',
            'CardHolder' => 'Card Holder',
            'CardExp' => 'Card Exp',
            'BankName' => 'Bank Name',
            'IPAddressUser' => 'Ip Address User',
            'CountryUser' => 'Country User',
            'CityUser' => 'City User',
            'UserClickPay' => 'User Click Pay',
            'CountSendOK' => 'Count Send Ok',
            'SendKvitMail' => 'Send Kvit Mail',
            'IdAgent' => 'Id Agent',
            'GisjkhGuid' => 'Gisjkh Guid',
            'TypeWidget' => 'Type Widget',
            'Bank' => 'Bank',
            'IsAutoPay' => 'Is Auto Pay',
            'AutoPayIdGate' => 'Auto Pay Id Gate',
            'UrlFormPay' => 'Url Form Pay',
            'UserUrlInform' => 'User Url Inform',
            'UserKeyInform' => 'User Key Inform',
            'SuccessUrl' => 'Success Url',
            'FailedUrl' => 'Failed Url',
            'CancelUrl' => 'Cancel Url',
            'PostbackUrl' => 'Postback Url',
            'sms_accept' => 'Sms Accept',
            'Dogovor' => 'Dogovor',
            'FIO' => 'Fio',
            'UserEmail' => 'User Email',
            'RCCode' => 'Rc Code',
        ];
    }

    public function getUslugatovar()
    {
        return $this->hasOne(Uslugatovar::className(), ['ID' => 'IdUsluga']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['ID' => 'IdUser']);
    }

    public function getPartner()
    {
        return $this->hasOne(Partner::className(), ['ID' => 'IdOrg']);
    }

    public function getBank()
    {
        return $this->hasOne(Bank::className(), ['ID' => 'Bank']);
    }

    public function getLog()
    {
        return $this->hasMany(PaySchetLog::className(), ['PaySchetId' => 'ID']);
    }

    public function getNotifications()
    {
        return $this->hasMany(NotificationPay::className(), ['IdPay' => 'ID']);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $this->DateLastUpdate = time();
        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @return bool
     */
    public function isOld()
    {
        return ($this->DateCreate + $this->TimeElapsed) < time();
    }

    /**
     * @return int
     */
    public function getSummFull()
    {
        return $this->SummPay + $this->ComissSumm;
    }

    /**
     * @param PartnerBankGate $partnerBankGate
     * @return $this
     * @throws GateException
     */
    public function changeGate(PartnerBankGate $partnerBankGate)
    {
        $uslugatovar = $this->partner->getUslugatovars()->where([
            'IsCustom' => $partnerBankGate->TU,
            'IsDeleted' => 0,
        ])->one();

        if(!$uslugatovar) {
            throw new GateException('Нет услуги');
        }

        $this->Bank = $partnerBankGate->BankId;
        $this->link('uslugatovar', $uslugatovar);
        $this->save(false);

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderdoneUrl()
    {
        return Yii::$app->params['domain'] . '/pay/orderdone/' . $this->ID;
    }

    /**
     * @param string $message
     */
    public function setError(string $message)
    {
        $this->Status = 2;
        $this->ErrorInfo = $message;
        $this->save(false);
    }

    /**
     * @return string
     */
    public function getFormatSummPay()
    {
        return sprintf("%02.2f", $this->SummPay / 100.0);
    }
}
