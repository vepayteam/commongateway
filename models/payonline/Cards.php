<?php

namespace app\models\payonline;

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
 */
class Cards extends \yii\db\ActiveRecord
{
    public const BRANDS = ['VISA', 'MASTERCARD', 'MIR', 'AMERICAN EXPRESS', 'JCB', 'DINNERSCLUB', 'MAESTRO', 'DISCOVER', 'CHINA UNIONPAY'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cards';
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

        //$tcBank = new TCBank();
        //$info = $tcBank->getCardInfo(['card' => $card]);
    }

    /**
     * Тип карты
     * @param $CardNumber
     * @return int 0 - visa, 1 - mastercard 2 - mir 3 - american express 4 - JCB 5 - Dinnersclub 6 - Maestro 7 - Discover 8 - China UnionPay
     */
    public static function GetTypeCard($CardNumber)
    {
        /*
         Номера карт начинаются с
            2-Мир
            3- American Express, JCB International, Diners Club
            ____30,36,38-Diners Club
            ____31,35-JCB International
            ____34,37-American Express
            4- VISA
            5- MasterCard, Maestro
            ____50,56,57,58-Maestro
            ____51,52,53,54,55-MasterCard
            6- Maestro, China UnionPay, Discover
            ____60-Discover
            ____62 - China UnionPay
            ____63, 67 - Maestro
            7-УЭК
         */
        if (mb_substr($CardNumber, 0, 1) == '4') {
            return 0; //visa
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['51', '52', '53', '54', '55'])) {
            return 1; //mastercard
        } elseif (mb_substr($CardNumber, 0, 1) == '2') {
            return 2; //mir
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['50', '56', '57', '58', '63', '67'])) {
            return 6; //Maestro
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['34', '37'])) {
            return 3; //american express
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['31', '35'])) {
            return 4; //JCB
        } elseif (in_array(mb_substr($CardNumber, 0, 2), ['30', '36', '38'])) {
            return 5; //Dinnersclub
        } elseif (mb_substr($CardNumber, 0, 2) == '60') {
            return 7; //Discover
        } elseif (mb_substr($CardNumber, 0, 2) == '62') {
            return 8; //China UnionPay
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
            $type = self::BRANDS[$CardType];
        }
        return $type;
    }

}
