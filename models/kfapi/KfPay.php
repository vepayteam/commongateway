<?php

namespace app\models\kfapi;

use app\models\bank\TCBank;
use app\models\payonline\Cards;
use app\models\TU;
use app\services\LanguageService;
use Yii;
use yii\base\Model;

class KfPay extends Model
{
    const SCENARIO_FORM = "form";
    const SCENARIO_AUTO = "auto";
    const SCENARIO_STATE = "state";

    const AFTMINSUMM = 1200;

    public $amount = 0;
    public $document_id = '';
    public $fullname = '';
    public $extid = '';
    public $descript = '';
    public $id;
    //public $type = 0;/*'type', */
    public $card = 0;
    public $timeout = 15;
    public $successurl = '';
    public $failurl = '';
    public $cancelurl = '';
    public $postbackurl = '';
    public $postbackurl_v2 = '';
    public $language = LanguageService::API_LANG_RUS;

    public function rules()
    {
        return [
            [['amount'], 'number', 'min' => 1, 'max' => 1000000, 'on' => [self::SCENARIO_FORM, self::SCENARIO_AUTO]],
            [['extid'], 'string', 'max' => 40, 'on' => [self::SCENARIO_FORM, self::SCENARIO_AUTO]],
            [['document_id'], 'string', 'max' => 40, 'on' => [self::SCENARIO_FORM]],
            [['fullname'], 'string', 'max' => 80, 'on' => [self::SCENARIO_FORM]],
            [['postbackurl', 'postbackurl_v2'], 'url', 'on' => [self::SCENARIO_FORM, self::SCENARIO_AUTO]],
            [['postbackurl', 'postbackurl_v2'], 'string', 'max' => 300, 'on' => [self::SCENARIO_FORM, self::SCENARIO_AUTO]],
            [['successurl', 'failurl', 'cancelurl'], 'url', 'on' => [self::SCENARIO_FORM]],
            [['successurl', 'failurl', 'cancelurl'], 'string', 'max' => 1000, 'on' => [self::SCENARIO_FORM]],
            [['descript'], 'string', 'max' => 200, 'on' => [self::SCENARIO_FORM]],
            [['card'], 'integer', 'on' => self::SCENARIO_AUTO],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59, 'on' => [self::SCENARIO_FORM]],
            [['amount'/*, 'extid'*/], 'required', 'on' => self::SCENARIO_FORM],
            [['amount'/*, 'extid'*/, 'card'], 'required', 'on' => self::SCENARIO_AUTO],
            [['id'], 'integer', 'on' => self::SCENARIO_STATE],
            [['id'], 'required', 'on' => self::SCENARIO_STATE],
            [['language'], 'in', 'range' => LanguageService::ALL_API_LANG_LIST],
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * Услуга эквайринга еком или афт
     * @param $org
     * @param $typeUsl
     * @return false|string|null
     * @throws \yii\db\Exception
     */
    public function GetUslug($org, $typeUsl)
    {
        return Yii::$app->db->createCommand("
            SELECT `ID`
            FROM `uslugatovar`
            WHERE `IDPartner` = :IDMFO AND `IsCustom` = :TYPEUSL AND `IsDeleted` = 0
        ", [':IDMFO' => $org, ':TYPEUSL' => $typeUsl]
        )->queryScalar();
    }

    /**
     * Услуга автоплатежа
     * @param $org
     * @return false|string|null
     * @throws \yii\db\Exception
     */
    public function GetUslugAuto($org)
    {
        return Yii::$app->db->createCommand("
            SELECT `ID`
            FROM `uslugatovar`
            WHERE `IDPartner` = :IDMFO AND `IsCustom` = :TYPEUSL AND `IsDeleted` = 0
        ", [':IDMFO' => $org, ':TYPEUSL' => TU::$AVTOPLATECOM]
        )->queryScalar();
    }

    /**
     * Услуга интернет-эквайринга
     * @param $org
     * @return false|string|null
     * @throws \yii\db\Exception
     */
    public function GetUslugEcom($org)
    {
        return Yii::$app->db->createCommand("
            SELECT `ID`
            FROM `uslugatovar`
            WHERE `IDPartner` = :IDMFO AND `IsCustom` = :TYPEUSL AND `IsDeleted` = 0
        ", [':IDMFO' => $org, ':TYPEUSL' => TU::$ECOM]
        )->queryScalar();
    }

    /**
     * Услуга интернет-эквайринга для ЖКХ
     * @param $org
     * @return false|string|null
     * @throws \yii\db\Exception
     */
    public function GetUslugJkh($org)
    {
        return Yii::$app->db->createCommand("
            SELECT `ID`
            FROM `uslugatovar`
            WHERE `IDPartner` = :IDMFO AND `IsCustom` = :TYPEUSL AND `IsDeleted` = 0
        ", [':IDMFO' => $org, ':TYPEUSL' => TU::$JKH]
        )->queryScalar();
    }

    /**
     * @param $IdPay
     * @return string
     */
    public function GetPayForm($IdPay)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            return Yii::$app->params['domain'] . '/pay/form/' . $IdPay;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://'.$_SERVER['SERVER_NAME'].'/pay/form/' . $IdPay;
        } else {
            return 'https://api.vepay.online/pay/form/' . $IdPay;
        }
    }

    /**
     * Использовать шлюз AFT
     * @param $IdPartner
     * @param int $bank
     * @return bool|int
     * @throws \yii\db\Exception
     */
    public function IsAftGate($IdPartner, $bank = 2)
    {
        $res = Yii::$app->db->createCommand("
            SELECT `IsAftOnly`, `LoginTkbAft`
            FROM `partner`
            WHERE `ID` = :IDMFO
        ", [':IDMFO' => $IdPartner]
        )->queryOne();

        if ($res['IsAftOnly']) {
            return 1;
        }

        if ($bank == 3 || $bank == 2) {
            return 0;
        }
        return $this->amount > self::AFTMINSUMM;
    }

    /**
     * Выбор шлюза автоплатежа
     * @throws \yii\db\Exception
     * @return integer
     */
    public function GetAutopayGate()
    {
        //три платежа в сутки на одну карту по одному шлюзу, 7 шлюзов
        $res = Yii::$app->db->createCommand("
            SELECT `ID`, `AutoPayIdGate`
            FROM pay_schet
            WHERE
              `IdKard` = :IDCARD
              AND `IsAutoPay` = 1
              AND `DateCreate` BETWEEN :DATEFROM AND :DATETO
        ", [
            ':IDCARD' => $this->card,
            ':DATEFROM' => strtotime('today'),
            ':DATETO' => time()
        ])->query();

        $lastGate = 1;
        $cntPayGate = 0;
        while ($row = $res->read()) {
            if ($lastGate < $row['AutoPayIdGate']) {
                $lastGate = $row['AutoPayIdGate'];
                $cntPayGate = 1;
            } elseif ($lastGate == $row['AutoPayIdGate']) {
                $cntPayGate++;
            }
        }

        if ($lastGate <= 7 && $cntPayGate < 3) {
            return $lastGate;
        } elseif ($lastGate < 7 && $cntPayGate >= 3) {
            return $lastGate + 1;
        }
        return 0;
    }

}
