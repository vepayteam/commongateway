<?php

namespace app\models\payonline;

use app\models\mfo\DistributionReports;
use app\models\mfo\VyvodReestr;
use app\models\mfo\VyvodSystem;
use app\models\partner\admin\structures\VyvodSystemFilterParams;
use app\models\partner\admin\VoznagStat;
use app\models\partner\UserLk;
use app\models\payonline\active_query\UslugatovarQuery;
use app\models\sms\tables\AccessSms;
use app\services\partners\models\PartnerOption;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "partner".
 *
 * @property int $ID [int(10) unsigned]  partnery
 * @property string $Name [varchar(250)]  naimenovanie v sisteme
 * @property string $UrLico [varchar(250)]
 * @property string $UrState [int(1)]
 * @property string $INN [varchar(20)]
 * @property string $KPP [varchar(20)]
 * @property string $OGRN [varchar(20)]
 * @property string $UrAdres [varchar(1000)]  uridicheskii adres - index|oblast|raion|gorod|ylica|dom|ofis
 * @property string $PostAdres [varchar(1000)]  pochtovyii adres - index|oblast|raion|gorod|ylica|dom|ofis
 * @property int $DateRegister [int(10) unsigned]  data registracii
 * @property string $NumDogovor [varchar(20)]  -
 * @property string $DateDogovor [varchar(20)]  -
 * @property string $PodpisantFull [varchar(100)]  -
 * @property string $PodpisantShort [varchar(50)]  -
 * @property string $SignatoryShortDative [varchar(63)] - Short form of dative signatory
 * @property string $PodpDoljpost [varchar(100)]  -
 * @property string $PodpDoljpostRod [varchar(100)]  -
 * @property string $PodpOsnovan [varchar(100)]  -
 * @property string $PodpOsnovanRod [varchar(100)]  -
 * @property string $URLSite [varchar(200)]  sait
 * @property string $Phone [varchar(50)]  telefon
 * @property string $Email [varchar(50)]  pochta
 * @property string $KontTehFio [varchar(100)]  fio po teh voporosam
 * @property string $KontTehEmail [varchar(50)]  email po teh voporosam
 * @property string $KontTehPhone [varchar(50)]  phone po teh voporosam
 * @property string $KontFinansFio [varchar(100)]  fio po finansovym voporosam
 * @property string $KontFinansEmail [varchar(50)]  email po finansovym voporosam
 * @property string $KontFinansPhone [varchar(50)]  email po finansovym voporosam
 * @property string $RSchet [varchar(50)]  raschethyii schet
 * @property string $KSchet [varchar(50)]  kor schet
 * @property string $BankName [varchar(100)]  bank naimenovanie
 * @property string $BikBank [varchar(20)]  bik banka
 * @property bool $IsBlocked [tinyint(1) unsigned]  0 - aktiven 1 - zablokirovan
 * @property string $PaaswordApi [varchar(100)]  parol api mfo
 * @property string $IpAccesApi
 * @property string $LoginTkbAft
 * @property string $KeyTkbAft
 * @property string $LoginTkbEcom
 * @property string $KeyTkbEcom
 * @property string $LoginTkbVyvod
 * @property string $KeyTkbVyvod
 * @property string $LoginTkbJkh
 * @property string $KeyTkbJkh
 * @property string $LoginTkbParts
 * @property string $KeyTkbParts
 * @property string $SchetTcb
 * @property string $SchetTcbTransit
 * @property string $SchetTcbNominal
 * @property string $SchetTcbParts
 * @property string $LoginTkbAuto1
 * @property string $LoginTkbAuto2
 * @property string $LoginTkbAuto3
 * @property string $LoginTkbAuto4
 * @property string $LoginTkbAuto5
 * @property string $LoginTkbAuto6
 * @property string $LoginTkbAuto7
 * @property string $KeyTkbAuto1
 * @property string $KeyTkbAuto2
 * @property string $KeyTkbAuto3
 * @property string $KeyTkbAuto4
 * @property string $KeyTkbAuto5
 * @property string $KeyTkbAuto6
 * @property string $KeyTkbAuto7
 * @property string $LoginTkbPerevod
 * @property string $KeyTkbPerevod
 * @property string $LoginTkbOct
 * @property string $KeyTkbOct
 * @property bool $IsUnreserveComis
 * @property string $SchetTCBUnreserve
 * @property bool $IsMfo [tinyint(1) unsigned]  0 - aktiven 1 - zablokirovan
 * @property bool $IsDeleted [tinyint(1) unsigned]  0 - rabotaet 1 - udalen
 * @property bool $IsAftOnly [tinyint(1) unsigned]  1 - aft gate only
 * @property AccessSms accessSms
 * @property DistributionReports $distribution
 * @property integer $BalanceIn
 * @property integer $BalanceOut
 * @property integer $TypeMerchant [tinyint(1) unsigned] type merchanta: 0 - merchant 1 - partner
 * @property integer $VoznagVyplatDirect [tinyint(1) unsigned] voznag po vyplatam: 0 - oplata po schety cheta 1 - vyvod so scheta
 * @property string $LoginTkbOctVyvod
 * @property string $KeyTkbOctVyvod
 * @property string $LoginTkbOctPerevod
 * @property string $KeyTkbOctPerevod
 * @property integer $IsCommonSchetVydacha
 * @property string $EmailNotif
 * @property string $OrangeDataSingKey
 * @property string $OrangeDataConKey
 * @property string $OrangeDataConCert
 * @property integer $IsUseKKmPrint
 * @property string $MtsLogin
 * @property string $MtsPassword
 * @property string $MtsToken
 * @property string $Apple_MerchantID
 * @property string $Apple_PayProcCert
 * @property string $Apple_KeyPasswd
 * @property string $Apple_MerchIdentKey
 * @property string $Apple_MerchIdentCert
 * @property integer $IsUseApplepay
 * @property string $GoogleMerchantID
 * @property integer $IsUseGooglepay
 * @property string $SamsungMerchantID
 * @property integer $IsUseSamsungpay
 * @property string $MtsLoginAft
 * @property string $MtsPasswordAft
 * @property string $MtsTokenAft
 * @property string $MtsLoginJkh
 * @property string $MtsPasswordJkh
 * @property string $MtsTokenJkh
 * @property string $MtsLoginOct
 * @property string $MtsPasswordOct
 * @property string $MtsTokenOct
 * @property string $MtsPasswordParts
 * @property string $MtsTokenParts
 * @property int $BankForPaymentId
 * @property int $BankForTransferToCardId
 * @property int $RunaBankCid
 * @property Uslugatovar[] $uslugatovars
 * @property PartnerBankRekviz[] $partner_bank_rekviz Deprecated, use {@see Partner::$bankRekviz} instead.
 * @property string $Apple_displayName [varchar(100)]
 * @property string $MtsLoginEcom [varchar(255)]
 * @property string $MtsPasswordEcom [varchar(255)]
 * @property string $MtsTokenEcom [varchar(255)]
 * @property string $MtsLoginVyvod [varchar(255)]
 * @property string $MtsPasswordVyvod [varchar(255)]
 * @property string $MtsTokenVyvod [varchar(255)]
 * @property string $MtsLoginAuto [varchar(255)]
 * @property string $MtsPasswordAuto [varchar(255)]
 * @property string $MtsTokenAuto [varchar(255)]
 * @property string $MtsLoginPerevod [varchar(255)]
 * @property string $MtsPasswordPerevod [varchar(255)]
 * @property string $MtsTokenPerevod [varchar(255)]
 * @property string $MtsLoginOctVyvod [varchar(255)]
 * @property string $MtsPasswordOctVyvod [varchar(255)]
 * @property string $MtsTokenOctVyvod [varchar(255)]
 * @property string $MtsLoginOctPerevod [varchar(255)]
 * @property string $MtsPasswordOctPerevod [varchar(255)]
 * @property string $MtsTokenOctPerevod [varchar(255)]
 * @property string $MtsLoginParts [varchar(255)]
 * @property string $nameWithId
 *
 * @property-read PartnerBankRekviz $bankRekviz {@see Partner::getBankRekviz()}
 */
class Partner extends ActiveRecord
{
    public const SCENARIO_SELFREG = 'selfreg';
    public const VEPAY_ID = 1;

    public static $TypeContrag = ['Мерчант', 'Партнер'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'partner';
    }

    /**
     * {@inheritdoc}
     * @todo Легаси. Убрать из ActiveRecord в отдельную форму
     */
    public function rules()
    {
        return [
            [['Name'], 'required', 'on' => self::SCENARIO_DEFAULT],
            [['IsBlocked', 'UrState', 'IsMfo', 'IsUnreserveComis', 'TypeMerchant', 'VoznagVyplatDirect',
                'IsCommonSchetVydacha', 'IsUseKKmPrint',
                'IsUseApplepay', 'IsUseGooglepay', 'IsUseSamsungpay', 'BankForPaymentId'], 'integer'],
            [['UrAdres', 'PostAdres'], 'string', 'max' => 1000],
            [['UrAdres', 'PostAdres', 'Apple_PayProcCert'], 'string', 'max' => 1000],
            [['Name', 'UrLico'], 'string', 'max' => 250],
            [[
                /* @todo Удалить */
                'MtsLoginParts', 'MtsPasswordParts', 'MtsTokenParts',
                'MtsLoginAft', 'MtsPasswordAft', 'MtsTokenAft',
                'MtsLoginJkh', 'MtsPasswordJkh', 'MtsTokenJkh',
                'MtsLoginOct', 'MtsPasswordOct', 'MtsTokenOct',

                'MtsLoginEcom', 'MtsPasswordEcom', 'MtsTokenEcom',
                'MtsLoginVyvod', 'MtsPasswordVyvod', 'MtsTokenVyvod',
                'MtsLoginAuto', 'MtsPasswordAuto', 'MtsTokenAuto',
                'MtsLoginPerevod', 'MtsPasswordPerevod', 'MtsTokenPerevod',
                'MtsLoginOctVyvod', 'MtsPasswordOctVyvod', 'MtsTokenOctVyvod',
                'MtsLoginOctPerevod', 'MtsPasswordOctPerevod', 'MtsTokenOctPerevod',
            ], 'string', 'max' => 500
            ],
            [['URLSite', 'PodpisantFull', 'PodpDoljpost', 'PodpDoljpostRod', 'PodpOsnovan', 'PodpOsnovanRod',
                'KontTehFio', 'KontFinansFio', 'BankName', 'PaaswordApi', 'MtsLogin', 'MtsPassword', 'MtsToken',
                'Apple_MerchantID', 'Apple_displayName', 'Apple_KeyPasswd', 'Apple_MerchIdentKey', 'Apple_MerchIdentCert',
                'GoogleMerchantID', 'SamsungMerchantID'
            ], 'string', 'max' => 100],
            [['SignatoryShortDative'], 'string', 'max' => 63],
            [['KeyTkbAft', 'KeyTkbEcom', 'KeyTkbVyvod', 'KeyTkbPerevod', 'KeyTkbAuto1', 'KeyTkbAuto2',
                'KeyTkbAuto3', 'KeyTkbAuto4', 'KeyTkbAuto5', 'KeyTkbAuto6', 'KeyTkbAuto7', 'IpAccesApi', 'KeyTkbJkh',
                'KeyTkbOct', 'KeyTkbOctVyvod', 'KeyTkbOctPerevod', 'KeyTkbParts'
            ], 'string', 'max' => 300],
            [['PodpisantShort', 'RSchet', 'KSchet', 'Phone', 'Email', 'KontTehEmail', 'KontTehPhone',
                'KontFinansEmail', 'KontFinansPhone', 'LoginTkbAft', 'LoginTkbEcom', 'LoginTkbVyvod', 'LoginTkbPerevod',
                'LoginTkbAuto1', 'LoginTkbAuto2', 'LoginTkbAuto3', 'LoginTkbAuto4', 'LoginTkbAuto5', 'LoginTkbAuto6',
                'LoginTkbAuto7', 'LoginTkbJkh', 'LoginTkbOct', 'LoginTkbOctVyvod', 'LoginTkbOctPerevod', 'LoginTkbParts'
            ], 'string', 'max' => 50],
            [['Email', 'KontTehEmail', 'KontFinansEmail', 'EmailNotif'], '\app\models\EmailListValidator'],
            [['INN', 'KPP', 'BikBank', 'OGRN', 'NumDogovor', 'DateDogovor', 'SchetTcb', 'SchetTcbTransit', 'SchetTcbNominal', 'SchetTcbParts', 'SchetTCBUnreserve'], 'string', 'max' => 20],
            [['Name', 'UrLico', 'UrAdres', 'PostAdres', 'Phone'], 'required', 'on' => self::SCENARIO_SELFREG],
            [['INN', 'OGRN', 'PodpisantShort', 'PodpisantFull', 'PodpOsnovan', 'PodpOsnovanRod'], 'required', 'on' => self::SCENARIO_SELFREG, 'when' => function ($model) {
                return in_array($model->UrState, [0, 1]);
            }],
            [['KPP', 'PodpDoljpost', 'PodpDoljpostRod', 'BikBank', 'BankName', 'RSchet', 'KSchet'], 'required', 'on' => self::SCENARIO_SELFREG, 'when' => function ($model) {
                return $model->UrState == 0;
            }],
            [
                [
                    'OrangeDataSingKey',
                    'OrangeDataConKey',
                    'OrangeDataConCert',
                    'Apple_MerchIdentKey',
                    'Apple_MerchIdentCert',
                ],
                'file',
                'skipOnEmpty' => true,
                'mimeTypes' => [
                    'application/pkcs8', // .p8 .key
                    'application/pkix-cert', // .cer
                    'application/x-x509-ca-cert', // .crt .der
                    'application/x-x509-user-cert', // .crt
                ],
                'extensions' => ['key', 'crt', 'cer'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     * @todo Легаси. Убрать из ActiveRecord в отдельную форму
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'partnery',
            'Name' => 'Наименование компании',
            'UrLico' => 'Юридическое лицо (ФИО)',
            'TypeMerchant' => 'Тип контрагента',
            'INN' => 'ИНН',
            'KPP' => 'КПП',
            'OGRN' => 'ОГРН',
            'UrAdres' => 'Юридический адрес',
            'PostAdres' => 'Почтовый адрес',
            'URLSite' => 'Сайт',
            'Phone' => 'Телефон',
            'Email' => 'Электронная почта',
            'DateRegister' => 'data registracii',
            'NumDogovor' => 'Номер договора',
            'DateDogovor' => 'Дата заключения договора',
            'PodpisantFull' => 'ФИО подписанта полное',
            'PodpisantShort' => 'ФИО подписанта сокращенное',
            'SignatoryShortDative' => 'ФИО подписанта сокращенное дательном падеже (кому?)',
            'PodpDoljpost' => 'Должность',
            'PodpDoljpostRod' => 'В лице (должность)',
            'PodpOsnovan' => 'Основание подписи',
            'PodpOsnovanRod' => 'Договор подписан на основании',
            'IsBlocked' => 'Заблокирован',
            'PaaswordApi' => 'Ключ API',
            'LoginTkbAft' => 'Логин ТКБ AFT',
            'KeyTkbAft' => 'Пароль ТКБ AFT',
            'LoginTkbOct' => 'Логин ТКБ OCT и ПСР',
            'KeyTkbOct' => 'Пароль ТКБ OCT и ПСР',
            'LoginTkbEcom' => 'Логин ТКБ Ecom',
            'KeyTkbEcom' => 'Пароль ТКБ Ecom',
            'LoginTkbJkh' => 'Логин ТКБ ЖКХ',
            'KeyTkbJkh' => 'Пароль ТКБ ЖКХ',
            'LoginTkbVyvod' => 'Логин ТКБ Вывод со счета погашений',
            'KeyTkbVyvod' => 'Пароль ТКБ Вывод со счета погашений',
            'LoginTkbAuto1' => 'Логин ТКБ Автоплатеж-1',
            'KeyTkbAuto1' => 'Пароль ТКБ Автоплатеж-1',
            'LoginTkbAuto2' => 'Логин ТКБ Автоплатеж-2',
            'KeyTkbAuto2' => 'Пароль ТКБ Автоплатеж-2',
            'LoginTkbAuto3' => 'Логин ТКБ Автоплатеж-3',
            'KeyTkbAuto3' => 'Пароль ТКБ Автоплатеж-3',
            'LoginTkbAuto4' => 'Логин ТКБ Автоплатеж-4',
            'KeyTkbAuto4' => 'Пароль ТКБ Автоплатеж-4',
            'LoginTkbAuto5' => 'Логин ТКБ Автоплатеж-5',
            'KeyTkbAuto5' => 'Пароль ТКБ Автоплатеж-5',
            'LoginTkbAuto6' => 'Логин ТКБ Автоплатеж-6',
            'KeyTkbAuto6' => 'Пароль ТКБ Автоплатеж-6',
            'LoginTkbAuto7' => 'Логин ТКБ Автоплатеж-7',
            'KeyTkbAuto7' => 'Пароль ТКБ Автоплатеж-7',
            'LoginTkbPerevod' => 'Логин ТКБ Перевод со счета погашений',
            'KeyTkbPerevod' => 'Пароль ТКБ Перевод со счета погашений',
            'IsUnreserveComis' => 'Возмещать комиссию МФО',
            'SchetTCBUnreserve' => 'Счет ТКБ для возмещения комиссии',
            'IpAccesApi' => 'IP адреса доступа к АПИ',
            'IsMfo' => 'Является МФО',
            'SchetTcb' => 'Номер транзитного счета ТКБ на выдачу',
            'SchetTcbTransit' => 'Номер транзитного счета ТКБ на погашение',
            'SchetTcbNominal' => 'Номер номинального счета ТКБ',
            'IsDeleted' => '0 - rabotaet 1 - udalen',
            'BankName' => 'Наименование банка',
            'BikBank' => 'БИК банка',
            'RSchet' => 'Расчетный счет',
            'KSchet' => 'Кор.счет банка',
            'KontTehFio' => 'ФИО менеджера',
            'KontTehPhone' => 'Телефон менеджера',
            'KontFinansPhone' => 'Телефон бухгалтера',
            'KontFinansFio' => 'ФИО бухгалтера',
            'KontTehEmail' => 'E-mail менеджера',
            'KontFinansEmail' => 'E-mail бухгалтера',
            'VoznagVyplatDirect' => 'Вывод вознаграждения по выдаче со счета МФО',
            'LoginTkbOctVyvod' => 'Логин ТКБ Вывод со счета выдачи',
            'KeyTkbOctVyvod' => 'Пароль ТКБ Вывод со счета выдачи',
            'LoginTkbOctPerevod' => 'Логин ТКБ Перевод со счета выдачи',
            'KeyTkbOctPerevod' => 'Пароль ТКБ Перевод со счета выдачи',
            'SchetTcbParts' => 'Номер счета разбивка платежей',
            'LoginTkbParts' => 'Логин ТКБ разбивка платежей',
            'KeyTkbParts' => 'Пароль ТКБ разбивка платежей',
            'IsCommonSchetVydacha' => 'Один счет на выдачу и погашение',
            'EmailNotif' => 'E-mail для оповещения',
            'OrangeDataSingKey' => 'Ключ для подписи',
            'OrangeDataConKey' => 'Ключ для подключения',
            'OrangeDataConCert' => 'Сертификат для подключения',
            'IsUseKKmPrint' => 'Отправлять чек на Email',

            'MtsLogin' => 'Логин МТС Банк',
            'MtsPassword' => 'Пароль МТС Банк',
            'MtsToken' => 'Токен МТС Банк',
            'MtsLoginJkh' => 'Логин МТС Банк ЖКХ',
            'MtsPasswordJkh' => 'Пароль МТС Банк ЖКХ',
            'MtsTokenJkh' => 'Токен МТС Банк ЖКХ',
            'MtsLoginAft' => 'Логин МТС Банк AFT',
            'MtsPasswordAft' => 'Пароль МТС Банк AFT',
            'MtsTokenAft' => 'Токен МТС Банк AFT',
            'MtsLoginOct' => 'Логин МТС Банк OCT и ПСР',
            'MtsPasswordOct' => 'Пароль МТС Банк OCT и ПСР',
            'MtsTokenOct' => 'Токен МТС Банк OCT и ПСР',

            'MtsLoginEcom' => 'Логин МТС Банк Ecom',
            'MtsPasswordEcom' => 'Пароль МТС Банк Ecom',
            'MtsTokenEcom' => 'Токен МТС Банк Ecom',
            'MtsLoginVyvod' => 'Логин МТС Банк Вывод со счета погашений',
            'MtsPasswordVyvod' => 'Пароль МТС Банк Вывод со счета погашений',
            'MtsTokenVyvod' => 'Токен МТС Банк Вывод со счета погашений',
            'MtsLoginAuto' => 'Логин МТС Банк Автоплатеж',
            'MtsPasswordAuto' => 'Пароль МТС Банк Автоплатеж',
            'MtsTokenAuto' => 'Токен МТС Банк Автоплатеж',
            'MtsLoginPerevod' => 'Логин МТС Банк Перевод со счета погашений',
            'MtsPasswordPerevod' => 'Пароль МТС Банк Перевод со счета погашений',
            'MtsTokenPerevod' => 'Токен МТС Банк Перевод со счета погашений',
            'MtsLoginOctVyvod' => 'Логин МТС Банк Вывод со счета выдачи',
            'MtsPasswordOctVyvod' => 'Пароль МТС Банк Вывод со счета выдачи',
            'MtsTokenOctVyvod' => 'Токен МТС Банк Вывод со счета выдачи',
            'MtsLoginOctPerevod' => 'Логин МТС Банк Перевод со счета выдачи',
            'MtsPasswordOctPerevod' => 'Пароль МТС Перевод со счета выдачи',
            'MtsTokenOctPerevod' => 'Токен МТС Банк Перевод со счета выдачи',

            'MtsLoginParts' => 'Логин МТС Банк разбивка платежей',
            'MtsPasswordParts' => 'Пароль МТС Банк разбивка платежей',
            'MtsTokenParts' => 'Токен МТС Банк разбивка платежей',
            'Apple_MerchantID' => 'Apple MerchantID',
            'Apple_PayProcCert' => 'Payment Processing Certificate',
            'Apple_KeyPasswd' => 'Apple пароль закрытого ключа',
            'Apple_MerchIdentKey' => 'Merchant Identity Key',
            'Apple_MerchIdentCert' => 'Merchant Identity Certificate',
            'IsUseApplepay' => 'Используется Apple Pay',
            'GoogleMerchantID' => 'Google MerchantID',
            'IsUseGooglepay' => 'Используется Google Pay',
            'SamsungMerchantID' => 'Samsung MerchantID',
            'IsUseSamsungpay' => 'Используется Samsung Pay',
            'BankForPaymentId' => 'Банк для оплат',
        ];
    }

    /**
     * {@inheritdoc}
     * @todo Легаси. Убрать из ActiveRecord в отдельную форму
     */
    public function attributeHints()
    {
        return [
            'IpAccesApi' => 'Адреса через запятую, пример: 127.0.0.1,192.168.1.0/24'
        ];
    }

    /**
     * @deprecated Use {@see Partner::getBankRekviz()} instead.
     */
    public function getPartner_bank_rekviz(): ActiveQuery
    {
        return $this->hasMany(PartnerBankRekviz::class, ['IdPartner' => 'ID']);
    }

    /**
     * @return ActiveQuery
     * @see PartnerBankRekviz::getPartner()
     */
    public function getBankRekviz(): ActiveQuery
    {
        return $this->hasOne(PartnerBankRekviz::class, ['IdPartner' => 'ID'])->inverseOf('partner');
    }

    public function getPartnerDogovor(): ActiveQuery
    {
        return $this->hasMany(PartnerDogovor::class, ['IdPartner' => 'ID']);
    }

    public function getPaySchets(): ActiveQuery
    {
        return $this->hasMany(PaySchet::class, ['IdOrg' => 'ID']);
    }

    public function getAccessSms(): ActiveQuery
    {
        return $this->hasOne(AccessSms::class, ['partner_id' => 'ID']);
    }

    public function getDistribution(): ActiveQuery
    {
        return $this->hasOne(DistributionReports::class, ['partner_id' => 'ID']);
    }

    /**
     * @return UslugatovarQuery
     */
    public function getUslugatovars(): ActiveQuery
    {
        return $this->hasMany(Uslugatovar::class, ['IDPartner' => 'ID']);
    }

    public function getOptions(): ActiveQuery
    {
        return $this->hasMany(PartnerOption::class, ['PartnerId' => 'ID']);
    }

    public function getBankGates(): ActiveQuery
    {
        return $this->hasMany(PartnerBankGate::class, ['PartnerId' => 'ID']);
    }

    public function getEnabledBankGates(): ActiveQuery
    {
        return $this->getBankGates()->where(['Enabled' => 1]);
    }

    public function getVyvodSystem(): ActiveQuery
    {
        return $this->hasMany(VyvodSystem::class, ['IdPartner' => 'ID']);
    }

    public function getVyvodReestr(): ActiveQuery
    {
        return $this->hasMany(VyvodReestr::class, ['IdPartner' => 'ID']);
    }

    public function getNameWithId(): string
    {
        return $this->ID . ' | ' . $this->Name;
    }

    /**
     * @param VyvodSystemFilterParams $params
     *
     * @return ActiveQuery
     */
    public function getSummVyveden(VyvodSystemFilterParams $params)
    {
        $query = $this->getVyvodSystem()
                      ->select(['SUM(`Summ`)'])
                      ->andWhere([
                          'or',
                          ['and', ['>=', 'DateFrom', $params->getDateFrom()], ['<=', 'DateTo', $params->getDateTo()]],
                          ['between', 'DateFrom', $params->getDateFrom(), $params->getDateTo()],
                          ['between', 'DateTo', $params->getDateFrom(), $params->getDateTo()],
                      ])
                      ->andWhere(['TypeVyvod' => $params->getTypeVyvod()]);

        return (($params->getFilterByStateOp() === true)
            ? $query->andWhere(['SatateOp' => [VoznagStat::OPERATION_STATE_IN_PROGRESS, VoznagStat::OPERATION_STATE_READY]])->cache(60 * 60)
            : $query->cache(60 * 60));
    }

    /**
     * @param VyvodSystemFilterParams $params
     *
     * @return ActiveQuery
     */
    public function getDataVyveden(VyvodSystemFilterParams $params)
    {
        $whereParams = [
            'and',
            ['<=', 'DateTo', $params->getDateTo()],
            ['TypeVyvod' => $params->getTypeVyvod()],
        ];

        if ($params->getFilterByStateOp() === true) {
            $whereParams[] = ['SatateOp' => [VoznagStat::OPERATION_STATE_IN_PROGRESS, VoznagStat::OPERATION_STATE_READY]];
        }

        $query = $this->getVyvodSystem()->select(['DateTo'])->andWhere($whereParams)->orderBy(['DateTo' => SORT_DESC]);

        return $query->cache(60 * 60);
    }

    /**
     * @param VyvodSystemFilterParams $params
     *
     * @return ActiveQuery
     */
    public function getSummPerechisl(VyvodSystemFilterParams $params)
    {
        $query = $this->getVyvodReestr()
                      ->select(['SUM(`SumOp`)'])
                      ->andWhere([
                          'or',
                          ['and', ['>=', 'DateFrom', $params->getDateFrom()], ['<=', 'DateTo', $params->getDateTo()]],
                          ['between', 'DateFrom', $params->getDateFrom(), $params->getDateTo()],
                          ['between', 'DateTo', $params->getDateFrom(), $params->getDateTo()],
                      ]);

        return (($params->getFilterByStateOp() === true)
            ? $query->andWhere(['StateOp' => [VoznagStat::OPERATION_STATE_IN_PROGRESS, VoznagStat::OPERATION_STATE_READY]])->cache(60 * 60)
            : $query->cache(60 * 60));
    }

    /**
     * @param VyvodSystemFilterParams $params
     *
     * @return ActiveQuery
     */
    public function getDataPerechisl(VyvodSystemFilterParams $params)
    {
        $whereParams = [
            'and',
            ['<=', 'DateTo', $params->getDateTo()],
        ];

        if ($params->getFilterByStateOp() === true) {
            $whereParams[] = ['StateOp' => [VoznagStat::OPERATION_STATE_IN_PROGRESS, VoznagStat::OPERATION_STATE_READY]];
        }

        $query = $this->getVyvodReestr()->select(['DateTo'])->andWhere($whereParams)->orderBy(['DateTo' => SORT_DESC]);

        return $query->cache(60 * 60);
    }
}