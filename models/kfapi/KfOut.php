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
            [['kbk', 'okato', 'paymentbase', 'taxperiod', 'taxdocnum', 'taxdocdate', 'taxpaymenttype'], 'string', 'max' => 200],
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
     * @return string
     */
    public function GetNdflXml()
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $AnsDocFnd = $dom->createElement("cft:AnsDocFnd");
        $AnsDocFnd->setAttribute("xmlns:cft", "CftFlowCollection");
        $dom->appendChild($AnsDocFnd);
        $b = $dom->createElement("cft:BEGIN_");
        $AnsDocFnd->appendChild($b);
        $documentList = $dom->createElement("cft:documentList");
        $b->appendChild($documentList);
        $b = $dom->createElement("cft:BEGIN_");
        $documentList->appendChild($b);
        $document = $dom->createElement("cft:document");
        $b->appendChild($document);
        $docb = $dom->createElement("cft:BEGIN_");
        $document->appendChild($docb);

        $payee = $dom->createElement("cft:payee");
        $docb->appendChild($payee);
        $bp = $dom->createElement("cft:BEGIN_");
        $payee->appendChild($bp);

        $inn = $dom->createElement("cft:inn");
        $inn->setAttribute('Value', $this->inn);
        $bp->appendChild($inn);

        $kpp = $dom->createElement("cft:kpp");
        $kpp->setAttribute('Value', $this->kpp);
        $bp->appendChild($kpp);

        $account = $dom->createElement("cft:account");
        $bp->appendChild($account);
        $ba = $dom->createElement("cft:BEGIN_");
        $account->appendChild($ba);

        $num = $dom->createElement("cft:num");
        $num->setAttribute('Value', $this->account);
        $ba->appendChild($num);

        $bankInfo = $dom->createElement("cft:bankInfo");
        $bp->appendChild($bankInfo);
        $bb = $dom->createElement("cft:BEGIN_");
        $bankInfo->appendChild($bb);

        $bic = $dom->createElement("cft:bic");
        $bic->setAttribute('Value', $this->bic);
        $bb->appendChild($bic);

        $budgetaryPmtInfo = $dom->createElement("cft:budgetaryPmtInfo");
        $docb->appendChild($budgetaryPmtInfo);
        $b = $dom->createElement("cft:BEGIN_");
        $budgetaryPmtInfo->appendChild($b);

        $budgetaryPmtInfo = $dom->createElement("cft:budgetaryPmtInfo");
        $b->appendChild($budgetaryPmtInfo);

        $formerStatus = $dom->createElement("cft:formerStatus");
        $formerStatus->setAttribute('Value', '0');
        $b->appendChild($formerStatus);

        $KBK = $dom->createElement("cft:KBK");
        $KBK->setAttribute('Value', $this->kbk);
        $b->appendChild($KBK);

        $OKATO = $dom->createElement("cft:OKATO");
        $OKATO->setAttribute('Value', $this->okato);
        $b->appendChild($OKATO);

        $paymentBase = $dom->createElement("cft:paymentBase");
        $paymentBase->setAttribute('Value', $this->paymentbase);
        $b->appendChild($paymentBase);

        $taxPeriod = $dom->createElement("cft:taxPeriod");
        $taxPeriod->setAttribute('Value', $this->taxperiod);
        $b->appendChild($taxPeriod);

        $taxDocNum = $dom->createElement("cft:taxDocNum");
        $taxDocNum->setAttribute('Value', $this->taxdocnum);
        $b->appendChild($taxDocNum);

        $taxDocDate = $dom->createElement("cft:taxDocDate");
        $taxDocDate->setAttribute('Value', $this->taxdocdate);
        $b->appendChild($taxDocDate);

        $taxPaymentType = $dom->createElement("cft:taxPaymentType");
        $taxPaymentType->setAttribute('Value', $this->taxpaymenttype);
        $b->appendChild($taxPaymentType);

        $summaKtAmount = $dom->createElement("cft:summaKtAmount");
        $docb->appendChild($summaKtAmount);
        $bs = $dom->createElement("cft:BEGIN_");
        $summaKtAmount->appendChild($bs);

        $amt = $dom->createElement("cft:amt");
        $amt->setAttribute('Value', $this->amount);
        $bs->appendChild($amt);

        $purpose = $dom->createElement("cft:purpose");
        $purpose->setAttribute('Value', $this->descript);
        $docb->appendChild($purpose);

        //die($dom->saveXML($AnsDocFnd));
        return ['req' => $dom->saveXML($AnsDocFnd)];

    }

}