<?php

namespace app\models\payonline;

use app\models\partner\admin\VoznagStat;
use app\models\payonline\active_query\UslugatovarQuery;
use app\models\TU;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "uslugatovar".
 *
 * @todo переименовать IsCustom
 *
 * @property string $ID
 * @property int $IDPartner [int(10) unsigned]  id partner
 * @property int $IsCustom [tinyint(1) unsigned]  Тип услуги
 * @property string $CustomData danuue customnogo
 * @property int $ExtReestrIDUsluga [int(10) unsigned]  id uslugi v reestrah
 * @property string $NameUsluga [varchar(200)]  naimenovanie uslugi
 * @property string $InfoUsluga [varchar(500)]  opisanie uslugi
 * @property string $SitePoint [varchar(50)]  sait ustanovki
 * @property string $ProfitExportFormat [varchar(250)]  format eksporta: LS, PERIOD, FIO, ADDRESS
 * @property string $SchetchikFormat [varchar(250)]  schetchiki uslugi, razdelenie |, format - regexp
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
 * @property bool $EnabledStatus [tinyint(1) unsigned]  0 - novaya 1 - activnaya 2 - zablokirovana
 * @property bool $IsDeleted [tinyint(1) unsigned]  0 - activen 1 - udalen
 * @property bool $HideFromList
 * @property int $IdMagazin [int(10) unsigned]  id partner_dogovor
 * @property string $EmailShablon tekst shablona uvedmlenia
 * @property string $ColorWdtMain [varchar(10)]  ocnovnoi cvet v vidgete
 * @property string $ColorWdtActive [varchar(10)]  cvet vydelenia v vidgete
 * @property PaySchet[] $paySchets
 * @property string $TypeExport [tinyint unsigned]  tip eksporta plateja: 0 - v teleport 1 - po banky po reestram 2 - online
 * @property string $TypeReestr [int unsigned]  tip reestra: 0 - teleport 1 - sber full 2 - sber gv 3 - sber hv 4 - kes 5 - ds kirov 6 - fkr43 7 - gaz 8 -
 *     sber new
 * @property string $IsKommunal [tinyint unsigned]  1 - jkh 0 - ecomm
 *
 * @property-read UslugatovarType $type {@see Uslugatovar::getType()}
 */
class Uslugatovar extends \yii\db\ActiveRecord
{
    const TYPE_REG_CARD = 1;
    const REG_CARD_ID = 1;
    const P2P = 26;

    public static $TypePay_str = [0 => 'Банковская карта', 1 => 'Банковская карта'];
    public static $TypeExport_str = [0 => 'в Vepay', 1 => 'в Банк по реестру', 2 => 'online'];
    public static $TypeReestr_str = [
        0 => 'Vepay',
    ];

    /**
     * @todo Удалить.
     */
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
        self::P2P => 'P2P перевод с карты на карту',

        110 => 'Погашение займа AFT с разбивкой',
        114 => 'Погашение займа ECOM с разбивкой',
        116 => 'Автоплатеж по займу ECOM с разбивкой',
        112 => 'Автоплатеж по займу AFT с разбивкой',
        102 => 'Оплата товара/услуги с разбивкой',
        100 => 'Оплата ЖКХ с разбивкой',
        119 => 'Перечисление по разбивке',

        UslugatovarType::H2H_POGASH_AFT => 'H2H Погашение займа AFT',
        UslugatovarType::H2H_POGASH_ECOM => 'H2H погашение займа ECOM',
        UslugatovarType::H2H_ECOM => 'H2H оплата товаров и услуг',
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
                'IdBankRekviz', 'TypeReestr', 'IsCustom', 'EnabledStatus', 'IdMagazin', 'HideFromList'], 'integer'],
            [['CustomData'], 'string'],
            [['PcComission', 'MinsumComiss', 'ProvVoznagPC', 'ProvVoznagMin', 'ProvComisPC', 'ProvComisMin'], 'number'],
            [['NameUsluga'], 'string', 'max' => 200],
            [['ProfitExportFormat', 'SchetchikFormat', 'SchetchikIzm',
                'Regex', 'PartnerSiteReferer', 'CommentsInfo'], 'string', 'max' => 250],
            [['InfoUsluga', 'Information', 'Labels', 'Comments', 'Example',
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
            'ProfitExportFormat' => 'Формат полей из реестра',
            'SchetchikFormat' => 'Формат счетчиков',
            'SchetchikIzm' => 'edinicy izmerenia schetchikov uslugi, razdelenie |',
            'PartnerSiteReferer' => 'referer dlia freima saita partnera po usluge',
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
            'ProvVoznagPC' => 'Комиссия с мерчанта %',
            'ProvVoznagMin' => 'Комиссия с мерчанта не менее, руб.',
            'ProvComisPC' => 'Комиссия банка %',
            'ProvComisMin' => 'Комиссия банка не менее, руб.',
            'TypeExport' => 'Тип экспорта',
            'ProfitIdProvider' => 'Провайдер Vepay',
            'TypeReestr' => 'Формат реестра',
            'EmailReestr' => 'Email для реестров',
            'IdBankRekviz' => 'Реквизиты банка',
            'HideFromList' => 'Скрыта из списка',
            'IsDeleted' => '0 - activen 1 - udalen',
            'LabelsInfo' => 'Подпись ввода для запроса',
            'CommentsInfo' => 'Комментарий для запроса',
            'ExampleInfo' => 'Пример ввода для запроса',
            'MaskInfo' => 'Маска ввода для запроса',
            'RegexInfo' => 'Регулярное выражение для запроса',
            'KodPoluchat' => 'Код получателя в реестре',
            'ReestrNameFormat' => 'Формат наименования реестра',
            'UrlInform' => 'Адрес для обратного запроса',
            'KeyInform' => 'Ключ обратного запроса',
        ];
    }

    public function attributeHints()
    {
        return [
            'ProfitExportFormat' => 'LS|PERIOD|FIO|ADDRESS',
            'SchetchikFormat' => 'HV1|GV1|HV2|GV2|ELE',
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

    /**
     * Gets query for [[PaySchets]].
     */
    public function getPaySchets(): ActiveQuery
    {
        return $this->hasMany(PaySchet::className(), ['IdUsluga' => 'ID']);
    }

    /**
     * Gets query for [[PaySchets]].
     */
    public function getCards(): ActiveQuery
    {
        return $this->hasOne(Cards::className(), ['ID' => 'IdKard'])->via('paySchets');
    }

    /**
     * Gets query for [[PaySchets]].
     */
    public function getPaySchetsBetween(): ActiveQuery
    {
        return $this->hasMany(PaySchet::className(), ['IdUsluga' => 'ID']);
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): UslugatovarQuery
    {
        return new UslugatovarQuery(get_called_class());
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if($this->ID) {
            TagDependency::invalidate(Yii::$app->cache, VoznagStat::STAT_DAY_TAG_PREFIX . $this->ID);
        }
        return parent::save($runValidation, $attributeNames);
    }

    public static function getPartsBalanceAccessCustoms(): array
    {
        // ID типов услуг с разбивкой
        return [
            TU::$POGASHATFPARTS,
            TU::$POGASHECOMPARTS,
            TU::$AVTOPLATECOMPARTS,
            TU::$AVTOPLATATFPARTS,
            TU::$ECOMPARTS,
            TU::$JKHPARTS,
            TU::$VYVODPAYSPARTS,
        ];
    }
}