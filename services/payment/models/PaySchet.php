<?php

namespace app\services\payment\models;

use app\models\payonline\Partner;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\services\CompensationService;
use app\services\notifications\models\NotificationPay;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\Banks;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\active_query\PaySchetQuery;
use Carbon\Carbon;
use Yii;

/**
 * This is the model class for table "pay_schet".
 *
 * @property int $ID
 * @property int $IdUser id user
 * @property int $IdKard id kard, esli privaizanoi kartoi oplata
 * @property int $IdUsluga id usluga
 * @property int $IdShablon id shablon
 * @property int $CurrencyId id currency
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
 * @property string|null $AddressUser Adress platelshika
 * @property string|null $ZipUser Zip kod platelshika
 * @property string|null $LoginUser Login platelshika
 * @property string|null $PhoneUser Telefon platelshika
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
 * @property string|null $Operations
 * @property Uslugatovar $uslugatovar
 * @property Partner $partner
 * @property Currency $currency
 * @property PaySchetLog[] $log
 * @property User $user
 * @property Bank $bank
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
    public $CntPays;

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

    public static function find()
    {
        return new PaySchetQuery(get_called_class());
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
                'IdAgent', 'TypeWidget', 'Bank', 'IsAutoPay', 'AutoPayIdGate', 'sms_accept', 'CurrencyId'
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
            'CurrencyId' => 'Id Currency',
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
            'AddressUser' => 'Address User',
            'LoginUser' => 'Login User',
            'PhoneUser' => 'Phone User',
            'ZipUser' => 'Zip code User',
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

    /**
     * @param int $statusIndex
     *
     * @return string
     */
    public static function getStatusTitle(int $statusIndex): string
    {
        return (array_key_exists($statusIndex, self::STATUSES) ? self::STATUSES[$statusIndex] : '');
    }

    public function getUslugatovar()
    {
        return $this->hasOne(Uslugatovar::class, ['ID' => 'IdUsluga']);
    }

    /**
     * Комиссия с клиента (для расчёта не из контекста модели)
     *
     * @param int   $sumPay
     * @param float $clientFeeCoefficient
     * @param float $minFee
     *
     * @return int
     * @todo Удалить - легаси, не используется.
     */
    public static function calcClientFeeStatic(int $sumPay, float $clientFeeCoefficient, float $minFee): int
    {
        $clientFee = round($sumPay * $clientFeeCoefficient / 100.0, 0);

        if ( $clientFee < $minFee * 100.0 ) {
            $clientFee = round($minFee * 100.0);
        }

        return $clientFee;
    }

    /**
     * Комиссия с клиента
     *
     * @return int
     * @todo Удалить - легаси, не используется.
     */
    public function calcClientFee(): int
    {
        return self::calcClientFeeStatic($this->SummPay, $this->uslugatovar->PcComission, $this->uslugatovar->MinsumComiss);
    }

    /**
     * Комиссия c мерчанта (вознаграждеие)
     *
     * @return int
     * @todo Удалить - легаси.
     */
    public function calcReward(): int
    {
        return $this->MerchVozn;
//        $reward = round($this->SummPay * $this->uslugatovar->ProvVoznagPC / 100.0, 0);
//
//        if ($reward < $this->uslugatovar->ProvVoznagMin * 100.0) {
//            $reward = $this->uslugatovar->ProvVoznagMin * 100.0;
//        }
//
//        return $reward;
    }

    /**
     * Комиссия банка (в коп)
     *
     * @return int
     * @todo Удалить - легаси, не используется.
     */
    public function calcBankFee(): int
    {
        $bankFee = round($this->getSummFull() * $this->uslugatovar->ProvComisPC / 100, 0);

        if ($bankFee < $this->uslugatovar->ProvComisMin * 100.0) {
            $bankFee = $this->uslugatovar->ProvComisMin * 100.0;
        }

        return $bankFee;
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['ID' => 'IdUser']);
    }

    public function getPartner()
    {
        return $this->hasOne(Partner::class, ['ID' => 'IdOrg']);
    }

    public function getBank()
    {
        return $this->hasOne(Bank::class, ['ID' => 'Bank']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['Id' => 'CurrencyId'])->one();
    }

    public function getLog()
    {
        return $this->hasMany(PaySchetLog::class, ['PaySchetId' => 'ID']);
    }

    public function getNotifications()
    {
        return $this->hasMany(NotificationPay::class, ['IdPay' => 'ID']);
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->DateLastUpdate = time();

        if ($insert) {
            // Считаем отчисления (комиссии) для платежа.
            /** @var CompensationService $compensationService */
            $compensationService = \Yii::$app->get(CompensationService::class);
            $gate = (new BankAdapterBuilder())
                ->build($this->partner, $this->uslugatovar, $this->currency)
                ->getPartnerBankGate();
            $this->ComissSumm = round($compensationService->calculateForClient($this, $gate));
            $this->BankComis = round($compensationService->calculateForBank($this, $gate));
            $this->MerchVozn = round($compensationService->calculateForPartner($this, $gate));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function save($runValidation = true, $attributeNames = null): bool
    {
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
     * @return string
     */
    public function getFromUrl()
    {
        if($this->Bank == Banks::REG_CARD_BY_OUT_ID) {
            return Yii::$app->params['domain'] . '/mfo/default/outcard/' . $this->ID;
        } else {
            return Yii::$app->params['domain'] . '/pay/form/' . $this->ID;
        }

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

    /**
     * @return bool
     */
    public function isNeedContinueRefreshStatus()
    {
        $now = Carbon::now();
        $dateCreate = Carbon::createFromTimestamp($this->DateCreate);

        return $now < $dateCreate->addDays(3);
    }
}
