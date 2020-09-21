<?php

namespace app\models\partner\stat;

use app\models\partner\UserLk;
use app\models\TU;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\VarDumper;

class PayShetStat extends Model
{
    public $IdPart = 0;
    public $usluga = [];
    public $TypeUslug = [];
    public $Extid = '';
    public $id = 0;
    public $summpay = 0;
    public $status = [];
    public $params = [];
    public $datefrom = '';
    public $dateto = '';

    public function rules()
    {
        return [
            [['IdPart', 'id'], 'integer'],
            [['summpay'], 'number'],
            [['Extid'], 'string', 'max' => 40],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['datefrom', 'dateto'], 'required'],
            [['usluga', 'status', 'TypeUslug'], 'each', 'rule' => ['integer']],
            [['params'], 'each', 'rule' => ['string']]
        ];
    }

    //после валидации - преобразуем данные в int - для запроса в бд.
    public function afterValidate()
    {
        foreach ($this->usluga as $key=>$val){
            $this->usluga[$key] = (int)$val;
        }
        foreach ($this->status as $key=>$val) {
            $this->status[$key] = (int)$val;
        }
        foreach ($this->TypeUslug as $key=>$val) {
            $this->TypeUslug[$key] = (int)$val;
        }
        parent::afterValidate(); // TODO: Change the autogenerated stub
    }

    public function attributeLabels()
    {
        return [
            'datefrom' => 'Период',
            'dateto' => 'Период',
            'summpay' => 'Сумма платежа',
            'id' => 'Идентификатор',
            'usluga' => 'Услуга'
        ];
    }

    /**
     * Список платежей
     *
     * @param int $IsAdmin
     * @param int $page
     * @param int $nolimit
     *
     * @return array
     */
    public function getList($IsAdmin, $page = 0, $nolimit = 0)
    {
        $CNTPAGE = 100;

        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $query = new Query();
        $query
            ->select([
                'ps.ID',
                'ps.Extid',
                'qp.NameUsluga',
                'ps.SummPay',
                'ps.ComissSumm',
                'ps.MerchVozn',
                'ps.BankComis',
                'ps.DateCreate',
                'ps.DateOplat',
                'ps.PayType',
                'ps.ExtBillNumber',
                'ps.Status',
                'ps.Period',
                'u.`UserDeviceType`',
                'ps.IdKard',
                'ps.CardType',
                'ps.QrParams',
                'ps.IdShablon',
                'ps.IdQrProv',
                'ps.IdAgent',
                'qp.IsCustom',
                'ps.ErrorInfo',
                'ps.BankName',
                'ps.CountryUser',
                'ps.CityUser',
                'qp.ProvVoznagPC',
                'qp.ProvVoznagMin',
                'qp.ProvComisPC',
                'qp.ProvComisMin',
                'ps.sms_accept',
                'ps.Dogovor',
                'ps.FIO',
                'ps.RCCode'
            ])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`banks` AS b', 'ps.Bank = b.ID')
            ->leftJoin('`uslugatovar` AS qp', 'ps.IdUsluga = qp.ID')
            ->leftJoin('`user` AS u', 'u.`ID` = ps.`IdUser`')
            ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => strtotime($this->datefrom . ":00"),
                ':DATETO' => strtotime($this->dateto . ":59")
            ]);

        if ($IdPart > 0) {
            $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        if (count($this->status) > 0) {
            $query->andWhere(['in', 'ps.Status', $this->status]);
        }
        if (count($this->usluga) > 0) {
            $query->andWhere(['in', 'ps.IdUsluga', $this->usluga]);
        }
        if (count($this->TypeUslug) > 0) {
            $query->andWhere(['in', 'qp.IsCustom', $this->TypeUslug]);
        }
        if ($this->id > 0) {
            $query->andWhere('ps.ID = :ID', [':ID' => $this->id]);
        }
        if (!empty($this->Extid)) {
            $query->andWhere('ps.Extid = :EXTID', [':EXTID' => $this->Extid]);
        }
        if ($this->summpay > 0) {
            $query->andWhere('ps.SummPay = :SUMPAY', [':SUMPAY' => round($this->summpay * 100.0)]);
        }
        if (count($this->params) > 0) {
            if (!empty($this->params[0])) $query->andWhere(['like', 'ps.Dogovor', $this->params[0]]);
        }

        $cnt = $sumPay = $sumComis = $voznagps = $bankcomis = 0;
        $allres = $query->cache(10)->all();

        foreach ($allres as $row) {
            $sumPay += $row['SummPay'];
            $sumComis += $row['ComissSumm'];
            $voznagps += $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];
            $bankcomis += $row['BankComis'];
            $cnt++;
        }

        if (!$nolimit) {
            if ($page > 0) {
                $query->offset($CNTPAGE * $page);
            }
            $query->orderBy('`ID` DESC')->limit($CNTPAGE);
        }
        $res = $query->cache(3)->all();

        $ret = [];
        foreach ($res as $row) {
            $row['VoznagSumm'] = $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];

            $ret[] = $row;
        }
        return ['data' => $ret, 'cnt' => $cnt, 'cntpage' => $CNTPAGE, 'sumpay' => $sumPay, 'sumcomis' => $sumComis, 'bankcomis' => $bankcomis, 'voznagps' => $voznagps];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * Отчет по платежам
     *
     * @param $IsAdmin
     *
     * @return array
     */
    public function getOtch($IsAdmin)
    {
        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $query = new Query();
        $query
            ->select([
                'ut.NameUsluga',
                'ut.ProvVoznagPC',
                'ut.ProvVoznagMin',
                'ps.SummPay',
                'ps.ComissSumm',
                'ps.MerchVozn',
                'ps.BankComis',
                'ps.IdUsluga',
                'ut.IsCustom',
                'ut.ProvVoznagPC',
                'ut.ProvVoznagMin',
                'ut.ProvComisPC',
                'ut.ProvComisMin'
            ])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`uslugatovar` AS ut', 'ps.IdUsluga = ut.ID')
            ->leftJoin('`banks` AS b', 'ps.Bank = b.ID')
            ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => strtotime($this->datefrom . ":00"),
                ':DATETO' => strtotime($this->dateto . ":59")
            ])
            ->andWhere('ps.Status = 1');

        if ($IdPart > 0) {
            $query->andWhere('ut.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }

        if (count($this->usluga) > 0) {
            $query->andWhere(['in', 'ut.ID', $this->usluga]);
        }
        //if ($data['paytype'] >= 0) {
        //$query->andWhere('ps.PayType = :IDPAYTYPE', [':IDPAYTYPE' => $data['paytype']]);
        //}
        if (count($this->TypeUslug) > 0) {
            $query->andWhere(['in', 'ut.IsCustom', $this->TypeUslug]);
        }

        $res = $query->cache(10)->all();

        $ret = [];
        foreach ($res as $row) {
            $indx = $row['IdUsluga'];
            $row['VoznagSumm'] = $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];

            if (!isset($ret[$indx])) {
                $row['CntPays'] = 1;
                $ret[$indx] = $row;
            } else {
                $ret[$indx]['SummPay'] += $row['SummPay'];
                $ret[$indx]['ComissSumm'] += $row['ComissSumm'];
                $ret[$indx]['BankComis'] += $row['BankComis'];
                $ret[$indx]['VoznagSumm'] += $row['VoznagSumm'];
                $ret[$indx]['MerchVozn'] += $row['MerchVozn'];
                $ret[$indx]['CntPays']++;
            }
        }

        return $ret;
    }

    /**
     * Сумма перечислений в МФО
     * @param int $TypePerech
     * @return integer
     * @throws \yii\db\Exception
     */
    public function GetSummPepechislen($TypePerech)
    {
        $summPerechisl = Yii::$app->db->createCommand("
            SELECT
                SUM(`SumOp`)
            FROM
                `vyvod_reestr`
            WHERE
                `IdPartner` = :IDMFO
                AND `DateFrom` >= :DATEFROM
                AND `DateTo` <= :DATETO
                AND `StateOp` = 1
                AND `TypePerechisl` = :TYPEPERECH
            ORDER BY `DateTo` DESC
            LIMIT 1
        ", [
            ':IDMFO' => $this->IdPart,
            ':DATEFROM' => strtotime($this->datefrom . ":00"),
            ':DATETO' => strtotime($this->dateto . ":59"),
            ':TYPEPERECH' => $TypePerech
        ])->queryScalar();

        return (double)$summPerechisl;
    }

    /**
     * Сумма возвращенных платежей
     * @return integer
     * @throws \yii\db\Exception
     */
    public function GetSummVozvrat()
    {
        $summVozvr = Yii::$app->db->createCommand("
            SELECT
                SUM(ps.`SummPay`)
            FROM
                `pay_schet` AS ps
                LEFT JOIN `uslugatovar` AS ut ON ps.IdUsluga = ut.ID
            WHERE
                ut.IDPartner = :IDMFO
                AND ps.`DateCreate` BETWEEN :DATEFROM AND :DATETO
                AND `Status` = 3
                AND ut.`IsCustom` IN (".implode(',', TU::InAll()).")
        ", [
            ':IDMFO' => $this->IdPart,
            ':DATEFROM' => strtotime($this->datefrom . ":00"),
            ':DATETO' => strtotime($this->dateto . ":59")])->queryScalar();

        return (double)$summVozvr;
    }

}