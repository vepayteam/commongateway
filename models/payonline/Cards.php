<?php

namespace app\models\payonline;

use app\helpers\Validators;
use app\models\payonline\active_query\CardActiveQuery;
use app\services\cards\models\PanToken;
use app\services\payment\models\active_query\PaySchetQuery;
use app\services\payment\models\Bank;
use app\services\payment\models\PaySchet;
use Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "cards".
 *
 * @property string $ID
 * @property string $IdUser
 * @property string $NameCard
 * @property string $ExtCardIDP
 * @property string $CardNumber
 * @property integer $CardType
 * @property integer $TypeCard
 * @property string $SrokKard
 * @property integer $Status
 * @property string $DateAdd
 * @property integer $CheckSumm
 * @property integer $Default
 * @property string $CardHolder
 * @property integer $IdPan
 * @property integer $IdBank
 * @property integer $IsDeleted
 * @property User $user
 * @property Bank $bank
 * @property PanToken $panToken
 *
 * @property-read PaySchet[] $paySchets {@see Cards::getPaySchets()}
 *
 * @todo Rename class from "Cards" to "Card".
 */
class Cards extends ActiveRecord
{
    public const BRAND_NAMES = [
        self::BRAND_VISA => 'VISA',
        self::BRAND_MASTERCARD => 'MASTERCARD',
        self::BRAND_MIR => 'MIR',
        self::BRAND_AMERICAN_EXPRESS => 'AMERICAN EXPRESS',
        self::BRAND_JCB => 'JCB',
        self::BRAND_DINNERSCLUB => 'DINNERSCLUB',
        self::BRAND_MAESTRO => 'MAESTRO',
        self::BRAND_DISCOVER => 'DISCOVER',
        self::BRAND_CHINA_UNIONPAY => 'CHINA UNIONPAY',
    ];

    public const BRAND_VISA = 0;
    public const BRAND_MASTERCARD = 1;
    public const BRAND_MIR = 2;
    public const BRAND_AMERICAN_EXPRESS = 3;
    public const BRAND_JCB = 4;
    public const BRAND_DINNERSCLUB = 5;
    public const BRAND_MAESTRO = 6;
    public const BRAND_DISCOVER = 7;
    public const BRAND_CHINA_UNIONPAY = 8;

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'cards';
    }

    /**
     * @return CardActiveQuery|ActiveQuery
     */
    public static function find()
    {
        return new CardActiveQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['IdUser', 'DateAdd'], 'required'],
            [[
                'IdUser', 'CardType', 'TypeCard', 'SrokKard', 'Status', 'DateAdd', 'CheckSumm', 'Default', 'IsDeleted',
                'IdPan', 'IdBank'
            ], 'integer'],
            [['NameCard', 'ExtCardIDP'], 'string', 'max' => 150],
            [['CardNumber'], 'string', 'max' => 40],
            [['CardHolder'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'ID' => 'ID',
            'IdUser' => 'Пользователь',
            'NameCard' => 'Наименование',
            'ExtCardIDP' => 'Внешний токен',
            'CardNumber' => 'Номер карты',
            'CardType' => 'Тип карты 0 - Visa; 1 - Mastercard; 2 - Mir',
            'TypeCard' => '0 - для оплаты; 1 - для пополнения',
            'CardHolder' => 'Держатель',
            'SrokKard' => 'Срок действия MMYY',
            'Status' => 'Статус карты: 0 - не подтверждена; 1 - активна; 2 - заблокирована',
            'DateAdd' => 'Дата добавления',
            'CheckSumm' => 'Контрольная сумма',
            'Default' => 'Default',
            'IdPan' => 'IdPan',
            'IdBank' => 'IdBank',
            'IsDeleted' => '0 - активна; 1 - удалена',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert): bool
    {
        if ($this->Default == 1) {
            Cards::updateAll(['Default' => 0], ['IdUser' => $this->IdUser, 'IsDeleted' => 0]);
        }

        if ($this->CardNumber) {
            $this->CardType = Cards::GetTypeCard($this->CardNumber);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return PaySchetQuery
     */
    public function getPaySchets(): ActiveQuery
    {
        return $this->hasMany(PaySchet::class, ['IdKard' => 'ID']);
    }

    /**
     * @todo Rename to "getUslugatovars". Add a readonly class property for this relation.
     */
    public function getUslugatovar(): ActiveQuery
    {
        /** {@see Cards::paySchets} */
        return $this->hasMany(Uslugatovar::class, ['ID' => 'IdUsluga'])->via('paySchets');
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['ID' => 'IdUser']);
    }

    public function getBank(): ActiveQuery
    {
        return $this->hasOne(Bank::class, ['ID' => 'IdBank']);
    }

    public function getPanToken(): ActiveQuery
    {
        return $this->hasOne(PanToken::class, ['ID' => 'IdPan']);
    }

    public function getMonth(): string
    {
        return mb_substr(sprintf('%04d', $this->SrokKard), 0, 2);
    }

    public function getYear(): string
    {
        return mb_substr(sprintf('%04d', $this->SrokKard), 2, 2);
    }

    public function allowUpdFields(): array
    {
        return [
            'NameCard',
            'Default'
        ];
    }

    public static function Convert()
    {
        $paySchets = PaySchet::find()
            ->where(['REGEXP', 'CardNum', '^[0123456789]{16,18}$'])
            ->all();

        /** @var PaySchet $paySchet */
        foreach ($paySchets as $paySchet) {
            $paySchet->CardNum = self::MaskCard($paySchet->CardNum);
            $paySchet->save();
        }
    }

    public static function MaskCard($card): string
    {
        if (strlen($card) == 16) {
            $card = substr($card, 0, 8) . '****' . substr($card, -4, 4);
        } else {
            $card = substr($card, 0, 8) . '******' . substr($card, -4, 4);
        }

        return $card;
    }

    public static function MaskCardLog($post)
    {
        // TKB
        if (preg_match('/\"CardNumber\":\"(\d+)\"/ius', $post, $m)) {
            $post = str_ireplace($m[1], self::MaskCard($m[1]), $post);
        }
        if (preg_match('/\"CVV\":\"(\d+)\"/ius', $post, $m)) {
            $post = str_ireplace($m[1], "***", $post);
        }
        if (preg_match('/\"CardNumberHash\":\"(\w+)\"/ius', $post, $m)) {
            $post = str_ireplace($m[1], "***", $post);
        }

        // BRS
        if (preg_match('/\"pan\":\"(\d+)\"/ius', $post, $m)) {
            $post = str_ireplace($m[1], self::MaskCard($m[1]), $post);
        }
        if (preg_match('/\"cvc2\":\"(\d+)\"/ius', $post, $m)) {
            $post = str_ireplace($m[1], "***", $post);
        }

        return $post;
    }

    /**
     * Masking card. If $data is array returns masked array, if string retuns masked string
     *
     * @param string|array $data
     * @return string|array
     */
    public static function maskCardUni($data)
    {
        try {
            return is_array($data) ? Json::decode(self::MaskCardLog(Json::encode($data))) : self::MaskCardLog((string)$data);
        } catch (Exception $e) {
            return $data;
        }
    }

    /**
     * Проверка номера карты
     *
     * @param string $s
     * @return bool
     */
    public static function CheckValidCard(string $s): bool
    {
        return Validators::checkByLuhnAlgorithm($s);
    }

    /**
     * Тип карты
     *
     * @param $cardNumber
     * @return int
     */
    public static function GetTypeCard($cardNumber): int
    {
        $firstLet = mb_substr($cardNumber, 0, 1);
        switch ($firstLet) {
            case '4':
                return self::BRAND_VISA;
            case '2':
                return self::BRAND_MIR;
        }

        $twoFirstLet = mb_substr($cardNumber, 0, 2);
        $brandList = [
            self::BRAND_MASTERCARD => ['51', '52', '53', '54', '55'],
            self::BRAND_MAESTRO => ['50', '56', '57', '58', '63', '67'],
            self::BRAND_AMERICAN_EXPRESS => ['34', '37'],
            self::BRAND_JCB => ['31', '35'],
            self::BRAND_DINNERSCLUB => ['30', '36', '38'],
            self::BRAND_DISCOVER => ['60'],
            self::BRAND_CHINA_UNIONPAY => ['62'],
        ];

        foreach ($brandList as $brand => $list) {
            if (in_array($twoFirstLet, $list)) {
                return $brand;
            }
        }

        return 0;
    }

    /**
     * Брэнд карты по типу
     *
     * @param int $cardType
     * @return string
     */
    public static function GetCardBrand(int $cardType): string
    {
        return self::BRAND_NAMES[$cardType];
    }

    /**
     * @return string|null Payment system name in upper case, e.g. MASTERCARD, VISA.
     */
    public function getPaymentSystemName(): ?string
    {
        return self::BRAND_NAMES[$this->CardType] ?? null;
    }
}