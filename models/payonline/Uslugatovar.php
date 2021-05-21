<?php

namespace app\models\payonline;

use app\models\partner\admin\VoznagStat;
use app\services\payment\models\UslugatovarType;
use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "uslugatovar".
 *
 * @property string $ID
 * @property int $IDPartner [int(10) unsigned]  id partner
 * @property bool $IsCustom [tinyint(1) unsigned]  0 - obshaia 1 - kastomnaya
 * @property string $CustomData danuue customnogo
 * @property int $ExtReestrIDUsluga [int(10) unsigned]  id uslugi v reestrah
 * @property string $NameUsluga [varchar(200)]  naimenovanie uslugi
 * @property string $InfoUsluga [varchar(500)]  opisanie uslugi
 * @property string $SitePoint [varchar(50)]  sait ustanovki
 * @property string $PatternFind [varchar(250)]  pattern dlia poiska provaidera po qr-cody
 * @property string $ProfitExportFormat [varchar(250)]  format eksporta: LS, PERIOD, FIO, ADDRESS
 * @property string $QrcodeExportFormat [varchar(500)]  qr code format eksporta: LS, PERIOD, FIO, ADDRESS
 * @property string $SchetchikFormat [varchar(250)]  schetchiki uslugi, razdelenie |, format - regexp
 * @property string $SchetchikNames [varchar(250)]  naimenovanie schetchikov uslugi, razdelenie |
 * @property string $SchetchikIzm [varchar(250)]  edinicy izmerenia schetchikov uslugi, razdelenie |
 * @property string $PartnerSiteReferer [varchar(250)]  referer dlia freima saita partnera po usluge
 * @property string $PcComission [double unsigned]  procent komissii
 * @property string $MinsumComiss [double unsigned]  minimalnaya komissiia v rub
 * @property string $Information [varchar(500)]  informacia po usluge
 * @property int $Group [int(10) unsigned]  id qr_group
 * @property int $Region [int(10) unsigned]  id uslugi_regions
 * @property string $LogoProv [varchar(100)]  logotip
 * @property int $MinSumm [int(10) unsigned]  minimalnaya symma plateja
 * @property int $MaxSumm [int(10) unsigned]  maksimalnaya symma plateja
 * @property string $Labels [varchar(500)]  podpis vvoda - |
 * @property string $Comments [varchar(500)]  kommentarii vvoda - |
 * @property string $Example [varchar(500)]  primer vvoda - |
 * @property string $Mask [varchar(100)]  maska vvoda - |
 * @property string $Regex [varchar(100)]  regularki - |||
 * @property string $LabelsInfo [varchar(300)]  podpis info - |
 * @property string $CommentsInfo [varchar(300)]  kommentarii info - |
 * @property string $ExampleInfo [varchar(100)]  primer info - |
 * @property string $MaskInfo [varchar(500)]  maska info - |
 * @property string $RegexInfo [varchar(300)]  regularki info - |||
 * @property string $ProvVoznagPC [double unsigned]  voznag %
 * @property string $ProvVoznagMin [double unsigned]
 * @property string $ProvComisPC [double unsigned]  prov komis %
 * @property string $ProvComisMin [double unsigned]
 * @property int $ProfitIdProvider [int(10) unsigned]  id systemgorod.providers
 * @property string $EmailReestr [varchar(100)]  email dlia reestra
 * @property string KodPoluchat
 * @property string ReestrNameFormat
 * @property int GroupReestrMain
 * @property string $UrlCheckReq [varchar(500)]  url dlia proverki vozmojnosti oplaty
 * @property string $UrlInform [varchar(500)]  url dlia informacii o plateje
 * @property string $KeyInform [varchar(20)]  key informacii o plateje
 * @property string $UrlReturn [varchar(500)]  url dlia vozvrata v magazin
 * @property string $UrlReturnFail [varchar(500)]  url dlia vozvrata v magazin pri oshibke
 * @property string $UrlReturnCancel [varchar(500)]  url dlia vozvrata v magazin pri otmene
 * @property string $SupportInfo [varchar(100)]  email slujby podderjki magazina
 * @property int $IdBankRekviz [int(10) unsigned]  id partner_bank_rekviz
 * @property bool $SendToGisjkh [tinyint(1) unsigned]  1 - otpravliat v gis jkh
 * @property bool $EnabledStatus [tinyint(1) unsigned]  0 - novaya 1 - activnaya 2 - zablokirovana
 * @property bool $IsDeleted [tinyint(1) unsigned]  0 - activen 1 - udalen
 * @property bool $HideFromList
 * @property int $IdMagazin [int(10) unsigned]  id partner_dogovor
 * @property string $EmailShablon tekst shablona uvedmlenia
 * @property string $ColorWdtMain [varchar(10)]  ocnovnoi cvet v vidgete
 * @property string $ColorWdtActive [varchar(10)]  cvet vydelenia v vidgete
 */
class Uslugatovar extends \yii\db\ActiveRecord
{
    const TYPE_REG_CARD = 1;
    const REG_CARD_ID = 1;

    public static $TypePay_str = [0 => 'Банковская карта', 1 => 'Банковская карта'];
    public static $TypeExport_str = [0 => 'в Vepay', 1 => 'в Банк по реестру', 2 => 'online'];
    public static $TypeReestr_str = [
        0 => 'Vepay',
    ];

    // TODO: use TU
    public static $TypeCustom_str = [
        self::TYPE_REG_CARD => 'Регистрация карты',
        11 => 'Выплата на счет',
        13 => 'Выдача займа на карту',
        10 => 'Погашение займа AFT',
        14 => 'Погашение займа ECOM',
        16 => 'Автоплатеж по займу ECOM',
        12 => 'Автоплатеж по займу AFT',
        2 => 'Оплата товара/услуги',
        0 => 'Оплата ЖКХ',
        17 => 'Комиссия',
        19 => 'Вывод средств',
        21 => 'Возмещение комисии',
        23 => 'Внутренний перевод между счетами',
        24 => 'Упрощенная идентификация пользователей',

        110 => 'Погашение займа AFT с разбивкой',
        114 => 'Погашение займа ECOM с разбивкой',
        116 => 'Автоплатеж по займу ECOM с разбивкой',
        112 => 'Автоплатеж по займу AFT с разбивкой',
        102 => 'Оплата товара/услуги с разбивкой',
        100 => 'Оплата ЖКХ с разбивкой',
        119 => 'Перечисление по разбивке',

    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uslugatovar';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['NameUsluga', 'ExtReestrIDUsluga', 'ProvVoznagPC', 'ProvVoznagMin', 'ProvComisPC', 'ProvComisMin',
                'PcComission', 'MinsumComiss'
                ], 'required'],
            [['ExtReestrIDUsluga', 'IsCustom', 'Group', 'Region', 'MinSumm', 'MaxSumm', 'TypeExport', 'ProfitIdProvider',
                'IdBankRekviz', 'TypeReestr', 'SendToGisjkh', 'IsCustom', 'EnabledStatus', 'IdMagazin', 'HideFromList'], 'integer'],
            [['CustomData'], 'string'],
            [['PcComission', 'MinsumComiss', 'ProvVoznagPC', 'ProvVoznagMin', 'ProvComisPC', 'ProvComisMin'], 'number'],
            [['NameUsluga'], 'string', 'max' => 200],
            [['PatternFind', 'ProfitExportFormat', 'SchetchikFormat', 'SchetchikNames', 'SchetchikIzm',
                'Regex', 'PartnerSiteReferer', 'CommentsInfo'], 'string', 'max' => 250],
            [['InfoUsluga', 'QrcodeExportFormat', 'Information', 'Labels', 'Comments', 'Example',
                'LabelsInfo', 'Mask', 'UrlInform', 'UrlReturn', 'UrlReturnFail', 'UrlReturnCancel', 'UrlCheckReq'], 'string', 'max' => 500],
            [['LogoProv', 'EmailReestr', 'ExampleInfo', 'MaskInfo', 'RegexInfo', 'SupportInfo','ReestrNameFormat'
                ], 'string', 'max' => 100],
            [['SitePoint'], 'string', 'max' => 50],
            [['ColorWdtMain', 'ColorWdtActive'], 'string', 'max' => 50],
            [['EmailShablon'], 'string', 'max' => 2000],
            [['ProfitIdProvider'], 'unique'],
            ['IsCustom', function ($attribute, $params) {
                if ($this->IDPartner > 0) {
                    $existModel = Uslugatovar::findOne(['IDPartner' => $this->IDPartner, 'IsCustom' => $this->IsCustom, 'IsDeleted' => 0]);
                    if ($existModel && $existModel->ID != $this->ID) {
                        $this->addError($attribute, 'Такая услуга уже существует');
                    }
                }
            }]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IDPartner' => 'id partner',
            'IdMagazin' => 'Магазин',
            'IsCustom' => 'Тип услуги',
            'CustomData' => 'danuue customnogo',
            'ExtReestrIDUsluga' => 'ID МФО для перечисления',
            'NameUsluga' => 'Наименование',
            'InfoUsluga' => 'Информация об услуге',
            'PatternFind' => 'Паттерн поиска по qr-коду',
            'ProfitExportFormat' => 'Формат полей из реестра',
            'SchetchikFormat' => 'Формат счетчиков',
            'SchetchikNames' => 'Подпись счетчиков (устар)',
            'SchetchikIzm' => 'edinicy izmerenia schetchikov uslugi, razdelenie |',
            'PartnerSiteReferer' => 'referer dlia freima saita partnera po usluge',
            'QrcodeExportFormat' => 'Формат экспорта по qr-коду',
            'PcComission' => 'Комиссия с клиента %',
            'MinsumComiss' => 'Комиссия с клиента не менее, руб.',
            'Information' => 'informacia po usluge',
            'Group' => 'Группа',
            'Region' => 'Регион',
            'LogoProv' => 'Логотип',
            'MinSumm' => 'Минимальная сумма платежа, в коп.',
            'MaxSumm' => 'Максимальна сумма платежа, в коп.',
            'Example' => 'Пример ввода',
            'Labels' => 'Подпись ввода',
            'Comments' => 'Комментарий',
            'Mask' => 'Маска ввода',
            'Regex' => 'Регулярное выражение',
            'ProvVoznagPC' => 'Вознаграждение Vepay %',
            'ProvVoznagMin' => 'Вознаграждение Vepay не менее, руб.',
            'ProvComisPC' => 'Комиссия банка %',
            'ProvComisMin' => 'Комиссия банка не менее, руб.',
            'TypeExport' => 'Тип экспорта',
            'ProfitIdProvider' => 'Провайдер Vepay',
            'TypeReestr' => 'Формат реестра',
            'EmailReestr' => 'Email для реестров',
            'IdBankRekviz' => 'Реквизиты банка',
            'HideFromList' => 'Скрыта из списка',
            'SendToGisjkh' => 'Отправлять в ГИС ЖКХ',
            'IsDeleted' => '0 - activen 1 - udalen',
            'LabelsInfo' => 'Подпись ввода для запроса',
            'CommentsInfo' => 'Комментарий для запроса',
            'ExampleInfo' => 'Пример ввода для запроса',
            'MaskInfo' => 'Маска ввода для запроса',
            'RegexInfo' => 'Регулярное выражение для запроса',
            'KodPoluchat' => 'Код получателя в реестре',
            'ReestrNameFormat' => 'Формат наименования реестра'
        ];
    }

    public function attributeHints()
    {
        return [
            'ProfitExportFormat' => 'LS|PERIOD|FIO|ADDRESS',
            'SchetchikFormat' => 'HV1|GV1|HV2|GV2|ELE',
            'QrcodeExportFormat' => 'par=<имя>=<подпись>|...###smk=<имя суммы> (par - параметр, per - период MMYY)',
        ];
    }

    public function getQr_group()
    {
        return $this->hasOne(QrGroup::className(), ['ID' => 'Group']);
    }

    public function getUslugi_regions()
    {
        return $this->hasOne(UslugiRegions::className(), ['Id' => 'Region']);
    }

    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getPartner()
    {
        return $this->hasOne(Partner::className(), ['ID' => 'IDPartner'])->one();
    }

    public function getPartner_bank_rekviz()
    {
        return $this->hasOne(PartnerBankRekviz::className(), ['ID' => 'IdBankRekviz']);
    }

    public function getType()
    {
        return $this->hasOne(UslugatovarType::className(), ['Id' => 'IsCustom']);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if($this->ID) {
            TagDependency::invalidate(Yii::$app->cache, VoznagStat::STAT_DAY_TAG_PREFIX . $this->ID);
        }
        return parent::save($runValidation, $attributeNames);
    }
}
