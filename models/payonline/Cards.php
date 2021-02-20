<?php

namespace app\models\payonline;

use app\models\payonline\active_query\CardActiveQuery;
use app\services\cards\models\PanToken;
use app\services\payment\models\Bank;
use Yii;

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
 */
class Cards extends \yii\db\ActiveRecord
{
    public const BRANDS = [
        'VISA' => 0,
        'MASTERCARD' => 1,
        'MIR' => 2,
        'AMERICAN EXPRESS' => 3,
        'JCB' => 4,
        'DINNERSCLUB' => 5,
        'MAESTRO' => 6,
        'DISCOVER' => 7,
        'CHINA UNIONPAY' => 8,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cards';
    }

    /**
     * @return CardActiveQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        return new CardActiveQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['IdUser', 'DateAdd'], 'required'],
            [['IdUser', 'CardType', 'TypeCard', 'SrokKard', 'Status', 'DateAdd', 'CheckSumm', 'Default', 'IsDeleted',
                'IdPan', 'IdBank'], 'integer'],
            [['NameCard', 'ExtCardIDP'], 'string', 'max' => 150],
            [['CardNumber'], 'string', 'max' => 40],
            [['CardHolder'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'IdUser' => 'Пользователь',
            'NameCard' => 'Наименование',
            'ExtCardIDP' => 'Внешний токен',
            'CardNumber' => 'Номер карты',
            'CardType' => 'tip karty: 0 - visa, 1 - mastercard 2 - mir',
            'TypeCard' => '0 - dlia oplaty 1 - dlia popolnenia',
            'CardHolder' => 'Держатель',
            'SrokKard' => 'Срок действия MMYY',
            'Status' => 'status karty: 0 - ne podtvejdena 1 - aktivna 2 - zablokirovana',
            'DateAdd' => 'data dobavlenia',
            'CheckSumm' => 'CheckSumm',
            'Default' => 'Default',
            'IdPan' => 'IdPan',
            'IdBank' => 'IdBank',
            'IsDeleted' => '0 - activna 1 - udalena',
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->Default == 1) {
            \Yii::$app->db->createCommand()
                ->update('cards',
                    ['Default' => 0],
                    ['IdUser' => $this->IdUser, 'IsDeleted' => 0])
            ->execute();
        }
        return parent::beforeSave($insert);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['ID' => 'IdUser']);
    }

    public function getBank()
    {
        return $this->hasOne(Bank::class, ['ID' => 'IdBank']);
    }

    public function allowUpdFields()
    {
        return [
            'NameCard',
            'Default'
        ];
    }

    public function getMonth()
    {
        return mb_substr(sprintf('%04d', $this->SrokKard), 0, 2);
    }

    public function getYear()
    {
        return mb_substr(sprintf('%04d', $this->SrokKard), 2, 2);
    }

    public static function Convert()
    {
        /*$res = \Yii::$app->db->createCommand("
            SELECT 
              `ID`,
              `ExtCardIDP`
            FROM
              `cards` 
            WHERE
                TypeCard = 1
                AND `ExtCardIDP` REGEXP '^[0123456789]{16,18}$'
        ")->query();
        while ($row = $res->read()) {
            $t = new \app\models\crypt\Tokenizer();
            $id = $t->CreateToken($row['ExtCardIDP'], 0);
            if ($id) {
                \Yii::$app->db->createCommand()->update('cards', ['ExtCardIDP' => $id, 'IdPan' => $id], '`ID` = :ID', ['ID' => $row['ID']])->execute();
            }
        }*/

        $res = \Yii::$app->db->createCommand("
            SELECT 
              `ID`,
              `CardNum`
            FROM
              `pay_schet` 
            WHERE
               `CardNum` REGEXP '^[0123456789]{16,18}$'
        ")->query();
        while ($row = $res->read()) {
            \Yii::$app->db->createCommand()
                ->update('pay_schet', ['CardNum' => self::MaskCard($row['CardNum'])],'`ID` = :ID', [':ID' => $row['ID']])
                ->execute();
        }
    }

    public static function MaskCard($card)
    {
        if (strlen($card) == 16) {
            $card = substr($card, 0, 6)."******".substr($card, -4, 4);
        } else {
            $card = substr($card, 0, 6)."********".substr($card, -4, 4);
        }
        return $card;
    }

    public static function MaskCardLog($post)
    {
        if (preg_match('/\"CardNumber\":\"(\d+)\"/ius', $post, $m)) {
            $post = str_ireplace($m[1], self::MaskCard($m[1]), $post);
        }
        if (preg_match('/\"CVV\":\"(\d+)\"/ius', $post, $m)) {
            $post = str_ireplace($m[1], "***", $post);
        }
        if (preg_match('/\"CardNumberHash\":\"(\w+)\"/ius', $post, $m)) {
            $post = str_ireplace($m[1], "***", $post);
        }
        return $post;
    }


    /**
     * Проверка номера карты
     * @param string $s
     * @return bool
     */
    public static function CheckValidCard($s)
    {
        // оставить только цифры
        $s = strrev(preg_replace('/[^\d]/','', $s));

        // вычисление контрольной суммы
        $sum = 0;
        for ($i = 0, $j = strlen($s); $i < $j; $i++) {
            // использовать четные цифры как есть
            if (($i % 2) == 0) {
                $val = $s[$i];
            } else {
                // удвоить нечетные цифры и вычесть 9, если они больше 9
                $val = $s[$i] * 2;
                if ($val > 9)  $val -= 9;
            }
            $sum += $val;
        }

        // число корректно, если сумма равна 10
        return (($sum % 10) == 0);
    }

    /**
     * Тип карты
     * @param $CardNumber
     * @return int 0 - visa, 1 - mastercard 2 - mir 3 - american express 4 - JCB 5 - Dinnersclub 6 - Maestro 7 - Discover 8 - China UnionPay
     */
    public static function GetTypeCard($CardNumber)
    {
        if (mb_substr($CardNumber, 0, 1) == '4') {
            return self::BRANDS['VISA']; //visa
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['51', '52', '53', '54', '55'])) {
            return self::BRANDS['MASTERCARD']; //mastercard
        } elseif (mb_substr($CardNumber, 0, 1) == '2') {
            return self::BRANDS['MIR']; //mir
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['50', '56', '57', '58', '63', '67'])) {
            return self::BRANDS['MAESTRO']; //Maestro
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['34', '37'])) {
            return self::BRANDS['AMERICAN EXPRESS']; //american express
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['31', '35'])) {
            return self::BRANDS['JCB']; //JCB
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['30', '36', '38'])) {
            return self::BRANDS['DINNERSCLUB']; //Dinnersclub
        } elseif (mb_substr($CardNumber, 0, 2) == '60') {
            return self::BRANDS['DISCOVER']; //Discover
        } elseif (mb_substr($CardNumber, 0, 2) == '62') {
            return self::BRANDS['CHINA UNIONPAY']; //China UnionPay
        }

        return 0;
    }

    /**
     * Брэнд карты по типу
     * @param $CardType
     * @return string
     */
    public static function GetCardBrand($CardType)
    {
        $type = '';
        if ($CardType >= 0 && $CardType <= 8) {
            foreach (self::BRANDS as $name => $v) {
                if($CardType == $v) {
                    return $name;
                }
            }
        }
        return $type;
    }

}
