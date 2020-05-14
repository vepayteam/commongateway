<?php

namespace app\models\payonline;

use app\models\mfo\DistributionReports;
use app\models\mfo\MfoSettings;
use app\models\partner\UserLk;
use app\models\sms\tables\AccessSms;
use app\models\TU;
use Yii;

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
 * @property string $SchetTcb
 * @property string $SchetTcbTransit
 * @property string $SchetTcbNominal
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
 * @property integer $IsAutoPerevodToVydacha
 * @property integer $IsCommonSchetVydacha
 * @property string $EmailNotif
 * @property string $Apple_MerchantID
 * @property string $Apple_PayProcCert
 * @property string $Apple_KeyPasswd
 *
 */
class Partner extends \yii\db\ActiveRecord
{
    public const SCENARIO_SELFREG = 'selfreg';

    public static $TypeContrag = ['Мерчант', 'Партнер'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'partner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Name'], 'required', 'on' => self::SCENARIO_DEFAULT],
            [['IsBlocked', 'UrState', 'IsMfo', 'IsAftOnly', 'IsUnreserveComis', 'TypeMerchant', 'VoznagVyplatDirect',
                'IsAutoPerevodToVydacha', 'IsCommonSchetVydacha'], 'integer'],
            [['UrAdres', 'PostAdres', 'Apple_PayProcCert'], 'string', 'max' => 1000],
            [['Name', 'UrLico'], 'string', 'max' => 250],
            [['URLSite', 'PodpisantFull', 'PodpDoljpost', 'PodpDoljpostRod', 'PodpOsnovan', 'PodpOsnovanRod',
                'KontTehFio', 'KontFinansFio', 'BankName', 'PaaswordApi', 'Apple_MerchantID', 'Apple_KeyPasswd'], 'string', 'max' => 100],
            [['KeyTkbAft', 'KeyTkbEcom', 'KeyTkbVyvod', 'KeyTkbPerevod', 'KeyTkbAuto1', 'KeyTkbAuto2',
                'KeyTkbAuto3', 'KeyTkbAuto4', 'KeyTkbAuto5', 'KeyTkbAuto6', 'KeyTkbAuto7', 'IpAccesApi', 'KeyTkbJkh',
                'KeyTkbOct', 'KeyTkbOctVyvod', 'KeyTkbOctPerevod'
            ], 'string', 'max' => 300],
            [['PodpisantShort', 'RSchet', 'KSchet', 'Phone', 'Email', 'KontTehEmail', 'KontTehPhone',
                'KontFinansEmail', 'KontFinansPhone', 'LoginTkbAft', 'LoginTkbEcom', 'LoginTkbVyvod', 'LoginTkbPerevod',
                'LoginTkbAuto1', 'LoginTkbAuto2', 'LoginTkbAuto3', 'LoginTkbAuto4', 'LoginTkbAuto5', 'LoginTkbAuto6',
                'LoginTkbAuto7', 'LoginTkbJkh', 'LoginTkbOct', 'LoginTkbOctVyvod', 'LoginTkbOctPerevod'
            ], 'string', 'max' => 50],
            [['Email', 'KontTehEmail', 'KontFinansEmail', 'EmailNotif'], '\app\models\EmailListValidator'],
            [['INN', 'KPP', 'BikBank', 'OGRN', 'NumDogovor', 'DateDogovor', 'SchetTcb', 'SchetTcbTransit', 'SchetTcbNominal', 'SchetTCBUnreserve'], 'string', 'max' => 20],
            [['Name', 'UrLico', 'UrAdres', 'PostAdres', 'Phone'], 'required', 'on' => self::SCENARIO_SELFREG],
            [['INN', 'OGRN', 'PodpisantShort', 'PodpisantFull', 'PodpOsnovan', 'PodpOsnovanRod'], 'required', 'on' => self::SCENARIO_SELFREG, 'when' => function($model) {
                return in_array($model->UrState, [0, 1]);
            }],
            [['KPP', 'PodpDoljpost', 'PodpDoljpostRod', 'BikBank', 'BankName', 'RSchet', 'KSchet'], 'required', 'on' => self::SCENARIO_SELFREG, 'when' => function($model) {
                return $model->UrState == 0;
            }]
        ];
    }

    /**
     * @inheritdoc
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
            'IsAftOnly' => 'Только AFT шлюз',
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
            'IsAutoPerevodToVydacha' => 'Автоперечисления на счет выдачи',
            'IsCommonSchetVydacha' => 'Один счет на выдачу и погашение',
            'EmailNotif' => 'E-mail для оповещения',
            'Apple_MerchantID' => 'Apple MerchantID',
            'Apple_PayProcCert' => 'Apple закрытый ключ сертификата',
            'Apple_KeyPasswd' => 'Apple пароль закрытого ключа',
        ];
    }

    public function attributeHints()
    {
        return [
            'IpAccesApi' => 'Адреса через запятую, пример: 127.0.0.1,192.168.1.0/24'
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->DateRegister = time();
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param $IdPart
     * @return null|Partner
     */
    public static function getPartner($IdPart)
    {
        if (!UserLk::IsAdmin(\Yii::$app->user)) {
            $IdPart = UserLk::getPartnerId(\Yii::$app->user);
        }

        return Partner::findOne($IdPart);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner_bank_rekviz()
    {
        return $this->hasMany(PartnerBankRekviz::className(), ['IdPartner' => 'ID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartnerDogovor()
    {
        return $this->hasMany(PartnerDogovor::className(), ['IdPartner' => 'ID']);
    }

    public function CreateUslug()
    {
        $usluga = new Uslugatovar();
        $usluga->IDPartner = $this->ID;
        $usluga->IsCustom = TU::$ECOM;
        $usluga->NameUsluga = "Оплата.".$this->Name;
        $usluga->PcComission = 2.2;
        $usluga->TypeExport = 1;
        $usluga->save(false);

        $usluga = new Uslugatovar();
        $usluga->IDPartner = $this->ID;
        $usluga->IsCustom = TU::$AVTOPLATECOM;
        $usluga->NameUsluga = "Автоплатеж.".$this->Name;
        $usluga->PcComission = 2.2;
        $usluga->ProvComisMin = 0.60;
        $usluga->TypeExport = 1;
        $usluga->save(false);

        $usluga = new Uslugatovar();
        $usluga->IDPartner = 1;
        $usluga->ExtReestrIDUsluga = $this->ID;
        $usluga->IsCustom = TU::$VYVODPAYS;
        $usluga->NameUsluga = "Перечисление." . $this->Name;
        $usluga->PcComission = 0;
        $usluga->ProvComisPC = 0;
        $usluga->ProvComisMin = 25;
        $usluga->TypeExport = 1;
        $usluga->save(false);
    }

    /**
     * Создание услуг МФО при регистрации
     * @param Partner $partner
     */
    public function CreateUslugMfo()
    {
        $usluga = new Uslugatovar();
        $usluga->IDPartner = $this->ID;
        $usluga->IsCustom = TU::$TOSCHET;
        $usluga->NameUsluga = "Выдача займа на счет." . $this->Name;
        $usluga->PcComission = 0;
        $usluga->TypeExport = 1;
        $usluga->ProvComisPC = 0.2;
        $usluga->ProvComisMin = 25;
        $usluga->ProvVoznagPC = 0.4;
        $usluga->ProvVoznagMin = 35;
        $usluga->save(false);

        $usluga = new Uslugatovar();
        $usluga->IDPartner = $this->ID;
        $usluga->IsCustom = TU::$TOCARD;
        $usluga->NameUsluga = "Выдача займа на карту." . $this->Name;
        $usluga->PcComission = 0;
        $usluga->TypeExport = 1;
        $usluga->ProvComisPC = 0.25;
        $usluga->ProvComisMin = 25;
        $usluga->ProvVoznagPC = 0.5;
        $usluga->ProvVoznagMin = 45;
        $usluga->save(false);

        $usluga = new Uslugatovar();
        $usluga->IDPartner = $this->ID;
        $usluga->IsCustom = TU::$POGASHATF;
        $usluga->NameUsluga = "Погашение займа AFT." . $this->Name;
        $usluga->PcComission = 2.2;
        $usluga->MinsumComiss = 0.01;
        $usluga->ProvComisPC = 0.5;
        $usluga->ProvComisMin = 25;
        $usluga->TypeExport = 1;
        $usluga->save(false);

        /*$usluga = new Uslugatovar();
        $usluga->IDPartner = $partner->ID;
        $usluga->IsCustom = TU::$AVTOPLATATF;
        $usluga->NameUsluga = "Автоплатеж по займу AFT.".$partner->Name;
        $usluga->PcComission = 2.2;
        $usluga->MinsumComiss = 0.60;
        $usluga->ProvComisPC = 0.7;
        $usluga->ProvComisMin = 40;
        $usluga->TypeExport = 1;
        $usluga->save(false);*/

        $usluga = new Uslugatovar();
        $usluga->IDPartner = $this->ID;
        $usluga->IsCustom = TU::$POGASHECOM;
        $usluga->NameUsluga = "Погашение займа ECOM." . $this->Name;
        $usluga->PcComission = 2.2;
        $usluga->MinsumComiss = 0.01;
        $usluga->ProvComisPC = 1.85;
        $usluga->ProvComisMin = 0;
        $usluga->TypeExport = 1;
        $usluga->save(false);

        $usluga = new Uslugatovar();
        $usluga->IDPartner = $this->ID;
        $usluga->IsCustom = TU::$AVTOPLATECOM;
        $usluga->NameUsluga = "Автоплатеж по займу ECOM." . $this->Name;
        $usluga->PcComission = 2.2;
        $usluga->MinsumComiss = 0.60;
        $usluga->ProvComisPC = 2;
        $usluga->ProvComisMin = 0.60;
        $usluga->TypeExport = 1;
        $usluga->save(false);

        $usluga = new Uslugatovar();
        $usluga->IDPartner = 1;
        $usluga->ExtReestrIDUsluga = $this->ID;
        $usluga->IsCustom = TU::$VYPLATVOZN;
        $usluga->NameUsluga = "Комиссия." . $this->Name;
        $usluga->PcComission = 0;
        $usluga->ProvComisPC = 0;
        $usluga->ProvComisMin = 25;
        $usluga->TypeExport = 1;
        $usluga->save(false);

        $usluga = new Uslugatovar();
        $usluga->IDPartner = 1;
        $usluga->ExtReestrIDUsluga = $this->ID;
        $usluga->IsCustom = TU::$VYVODPAYS;
        $usluga->NameUsluga = "Перечисление." . $this->Name;
        $usluga->PcComission = 0;
        $usluga->ProvComisPC = 0;
        $usluga->ProvComisMin = 25;
        $usluga->TypeExport = 1;
        $usluga->save(false);

        $usluga = new Uslugatovar();
        $usluga->IDPartner = 1;
        $usluga->ExtReestrIDUsluga = $this->ID;
        $usluga->IsCustom = TU::$REVERSCOMIS;
        $usluga->NameUsluga = "Возмещение комиссии." . $this->Name;
        $usluga->PcComission = 0;
        $usluga->ProvComisPC = 0;
        $usluga->ProvComisMin = 0;
        $usluga->TypeExport = 1;
        $usluga->save(false);

        $usluga = new Uslugatovar();
        $usluga->IDPartner = 1;
        $usluga->ExtReestrIDUsluga = $this->ID;
        $usluga->IsCustom = TU::$PEREVPAYS;
        $usluga->NameUsluga = "Перечисление на выдачу." . $this->Name;
        $usluga->PcComission = 0;
        $usluga->ProvComisPC = 0;
        $usluga->ProvComisMin = 0;
        $usluga->TypeExport = 1;
        $usluga->save(false);


    }

    public function getAccessSms(){
        return $this->hasOne(AccessSms::class, ['partner_id'=>'ID']);
    }

    /**
     * задает связь с моделью - DistributionReports
    */
    public function getDistribution()
    {
        return $this->hasOne(DistributionReports::class, ['partner_id'=>'ID']);
    }
}
