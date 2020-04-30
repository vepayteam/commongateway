<?php


namespace app\models\kfapi;

use app\models\payonline\Partner;
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

    public $sendername;
    public $senderadress;
    public $senderaccount;

    public $kbk;
    public $okato;
    public $paymentbase;
    public $taxperiod;
    public $taxdocnum;
    public $taxdocdate;
    public $taxpaymenttype;

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
    const SCENARIO_NDFL = 'ndfl';
    const SCENARIO_INT = 'int';
    const SCENARIO_STATE = 'state';

    public function rules()
    {
        return [
            [['cardnum'], 'match', 'pattern' => '/^\d{16}|\d{18}$/', 'on' => self::SCENARIO_CARD],
            [['card'], 'integer', 'on' => self::SCENARIO_CARDID],
            [['document_id'], 'string', 'max' => 40, 'on' => [self::SCENARIO_CARD]],
            [['fullname'], 'string', 'max' => 80, 'on' => [self::SCENARIO_CARD]],
            [['account'], 'match', 'pattern' => '/^\d{20}$/', 'on' => [self::SCENARIO_FL, self::SCENARIO_UL, self::SCENARIO_INT,self::SCENARIO_NDFL]],
            [['bic'], 'match', 'pattern' => '/^\d{9}$/', 'on' => [self::SCENARIO_FL, self::SCENARIO_UL]],
            [['descript'], 'string', 'max' => 210, 'on' => [self::SCENARIO_FL, self::SCENARIO_UL, self::SCENARIO_INT, self::SCENARIO_NDFL]],
            [['inn'], 'match', 'pattern' => '/^\d{10,13}$/', 'on' => [self::SCENARIO_UL, self::SCENARIO_INT, self::SCENARIO_NDFL]],
            [['kpp'], 'string', 'max' => 9, 'on' => [self::SCENARIO_UL, self::SCENARIO_INT,self::SCENARIO_NDFL]],
            [['name'], 'string', 'max' => 200, 'on' => [self::SCENARIO_UL, self::SCENARIO_INT,self::SCENARIO_NDFL]],
            [['fio'], 'string', 'max' => 150, 'on' => self::SCENARIO_FL],
            [['id'], 'integer', 'on' => self::SCENARIO_STATE],
            [['amount'], 'number', 'min' => 1, 'max' => 600000, 'on' => [self::SCENARIO_CARD, self::SCENARIO_CARDID]],
            [['amount'], 'number', 'min' => 1, 'max' => 21000000, 'on' => [self::SCENARIO_UL, self::SCENARIO_FL, self::SCENARIO_INT,self::SCENARIO_NDFL]],
            [['extid'], 'string', 'max' => 40],
            [['cardnum', 'amount'/*, 'extid'*/], 'required', 'on' => self::SCENARIO_CARD],
            [['card', 'amount'/*, 'extid'*/], 'required', 'on' => self::SCENARIO_CARDID],
            [['name', 'inn', 'account', 'bic', 'descript', 'amount'/*, 'extid'*/], 'required', 'on' => [self::SCENARIO_UL,self::SCENARIO_NDFL]],
            [['fio', 'account', 'bic', 'descript', 'amount'/*, 'extid'*/], 'required', 'on' => self::SCENARIO_FL],
            [['name', 'inn', 'account', 'descript', 'amount'/*, 'extid'*/], 'required', 'on' => [self::SCENARIO_INT,self::SCENARIO_NDFL]],
            [['sendername', 'senderadress', 'senderaccount', 'kbk', 'okato', 'paymentbase', 'taxperiod', 'taxdocnum', 'taxdocdate', 'taxpaymenttype'], 'string', 'max' => 200],
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

    /**
     * Создание Xml запроса
     *
     * @param $params
     * @param $partner
     * @return array
     */
    public function GetNdflJson(array $params, Partner $partner)
    {
        $ret = [
            "document" => [
                "uid" => md5($params['IdPay']),
                "date" => date("Y-m-d H:i:s"),
                "dateValue" => date("Y-m-d 00:00:00"),
                "extID" => $params['IdPay'],
                "filial" => "000",
                "num" => $params['IdPay'],
                "pack" => "VP",
                "purpose" => $this->descript,
                "docName" => [
                    "name" => "Оплата НДФЛ",
                    "operType" => "01"
                ],
                "sender" => [
                    "name" => $this->sendername."//".$this->senderadress."//".$this->senderaccount,
                    "account" => [
                        "number" => $partner->SchetTcbNominal,
                        "type" => "1"
                    ]
                ],
                "payee" => [
                    "inn" => $this->inn,
                    "kpp" => $this->kpp,
                    "name" => $this->name,
                    "account" => [
                        "number" => $this->account,
                        "type" => "1",
                        "bank" => [
                            "bic" => $this->bic
                        ]
                    ]
                ],
                "summaDt" => [
                    "amount" => $this->amount,
                    "currency" => "RUB"
                ],
                "merchantCheck" => [
                    "merchantId" => "LEMONONLINE",
                    "accountName" => $partner->SchetTcbNominal,
                ],
                "budgetaryPmt" => [
                    "formerStatus" => "01",
                    "kbk" => $this->kbk,
                    "okato" => $this->okato,
                    "paymentBase" => $this->paymentbase,
                    "taxPeriod" => $this->taxperiod,
                    "taxDocNum" => $this->taxdocnum,
                    "taxDocDate" => $this->taxdocdate,
                    "taxPaymentType" => $this->taxpaymenttype
                ]
	        ]
        ];

        return $ret;

    }

}