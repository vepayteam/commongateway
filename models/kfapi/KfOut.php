<?php


namespace app\models\kfapi;

use app\models\TU;
use Yii;
use yii\base\Model;

class KfOut extends Model
{
    public $amount = 0;
    public $extid = '';
    public $document_id = '';
    public $fullname = '';

    public $card = 0;
    public $cardnum = '';

    public $name;
    public $fio;
    public $inn = '';
    public $kpp = '';
    public $account;
    public $bic;
    public $descript;

    /**
     * Указывает на то, нужно ли подтверждать платежное поручение по смс.
     * 1 - да
     * 0 - нет, стандартное значение
     * Не обязательное.
    */
    public $sms = 0;

    public $id;

    const SCENARIO_CARD = 'card';
    const SCENARIO_CARDID = 'cardid';
    const SCENARIO_UL = 'ul';
    const SCENARIO_FL = 'fl';
    const SCENARIO_INT = 'int';
    const SCENARIO_STATE = 'state';

    public function rules()
    {
        return [
            [['cardnum'], 'match', 'pattern' => '/^\d{16}|\d{18}$/', 'on' => self::SCENARIO_CARD],
            [['card'], 'integer', 'on' => self::SCENARIO_CARDID],
            [['document_id'], 'string', 'max' => 40, 'on' => [self::SCENARIO_CARD]],
            [['fullname'], 'string', 'max' => 80, 'on' => [self::SCENARIO_CARD]],
            [['account'], 'match', 'pattern' => '/^\d{20}$/', 'on' => [self::SCENARIO_FL, self::SCENARIO_UL, self::SCENARIO_INT]],
            [['bic'], 'match', 'pattern' => '/^\d{9}$/', 'on' => [self::SCENARIO_FL, self::SCENARIO_UL]],
            [['descript'], 'string', 'max' => 210, 'on' => [self::SCENARIO_FL, self::SCENARIO_UL, self::SCENARIO_INT]],
            [['inn'], 'match', 'pattern' => '/^\d{10,13}$/', 'on' => [self::SCENARIO_UL, self::SCENARIO_INT]],
            [['kpp'], 'string', 'max' => 9, 'on' => [self::SCENARIO_UL, self::SCENARIO_INT]],
            [['name'], 'string', 'max' => 200, 'on' => [self::SCENARIO_UL, self::SCENARIO_INT]],
            [['fio'], 'string', 'max' => 150, 'on' => self::SCENARIO_FL],
            [['id'], 'integer', 'on' => self::SCENARIO_STATE],
            [['amount'], 'number', 'min' => 1, 'max' => 600000, 'on' => [self::SCENARIO_CARD, self::SCENARIO_CARDID]],
            [['amount'], 'number', 'min' => 1, 'max' => 21000000, 'on' => [self::SCENARIO_UL, self::SCENARIO_FL, self::SCENARIO_INT]],
            [['extid'], 'string', 'max' => 40, 'on' => [self::SCENARIO_CARD, self::SCENARIO_CARDID, self::SCENARIO_UL, self::SCENARIO_FL, self::SCENARIO_INT]],
            [['cardnum', 'amount'/*, 'extid'*/], 'required', 'on' => self::SCENARIO_CARD],
            [['card', 'amount'/*, 'extid'*/], 'required', 'on' => self::SCENARIO_CARDID],
            [['name', 'inn', 'account', 'bic', 'descript', 'amount'/*, 'extid'*/], 'required', 'on' => self::SCENARIO_UL],
            [['fio', 'account', 'bic', 'descript', 'amount'/*, 'extid'*/], 'required', 'on' => self::SCENARIO_FL],
            [['name', 'inn', 'account', 'descript', 'amount'/*, 'extid'*/], 'required', 'on' => self::SCENARIO_INT],
            [['id'], 'required', 'on' => self::SCENARIO_STATE],
            [['sms'], 'integer', 'on' => [self::SCENARIO_CARD, self::SCENARIO_UL, self::SCENARIO_FL, self::SCENARIO_INT]]
        ];
    }

    /**
     * Услуга выплаты на карту или счет (по scenario)
     * @param $org
     * @return false|string|null
     * @throws \yii\db\Exception
     */
    public function GetUslug($org)
    {
        return Yii::$app->db->createCommand("
            SELECT `ID` 
            FROM `uslugatovar`
            WHERE `IDPartner` = :IDMFO AND `IsCustom` = :TYPEUSL AND `IsDeleted` = 0
        ", [':IDMFO' => $org, ':TYPEUSL' => ($this->scenario == self::SCENARIO_CARD || $this->scenario == self::SCENARIO_CARDID) ? TU::$TOCARD : TU::$TOSCHET])->queryScalar();
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }
}