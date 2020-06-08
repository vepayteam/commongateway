<?php


namespace app\models\partner\admin;


use app\models\partner\UserLk;
use app\models\TU;
use Yii;
use yii\base\Model;
use yii\db\Query;

class VoznagStat extends Model
{
    public $datefrom;
    public $dateto;
    public $IdPart;
    public $TypeUslug = 0; //0 - все 1 - погашение 2 - выдача

    public function rules()
    {
        return [
            [['IdPart', 'TypeUslug'], 'integer'],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['datefrom', 'dateto'], 'required'],
        ];
    }

    /**
     * Отчет по мерчантам
     *
     * @param $IsAdmin
     * @return array
     * @throws \yii\db\Exception
     */
    public function GetOtchMerchant($IsAdmin)
    {
        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $dateFrom = strtotime($this->datefrom.":00");
        $dateTo = strtotime($this->dateto.":59");

        $query = new Query();
        $query
            ->select([
                'r.Name AS NamePartner',
                'ut.IDPartner',
                'ut.ProvVoznagPC',
                'ut.ProvVoznagMin',
                'SUM(ps.SummPay) AS SummPay',
                'SUM(ps.ComissSumm) AS ComissSumm',
                'SUM(ps.MerchVozn) AS MerchVozn',
                'SUM(ps.BankComis) AS BankComis',
                'COUNT(*) AS CntPays',
                'ps.IdUsluga',
                'ut.IsCustom',
                'ut.ProvVoznagPC',
                'ut.ProvVoznagMin',
                'ut.ProvComisPC',
                'ut.ProvComisMin',
                'r.VoznagVyplatDirect'
            ])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`uslugatovar` AS ut', 'ps.IdUsluga = ut.ID')
            ->leftJoin('`partner` AS r', 'r.ID = ut.IDPartner')
            ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => $dateFrom,
                ':DATETO' => $dateTo
            ])
            ->andWhere('ps.Status = 1');

        if ($IdPart > 0) {
            $query->andWhere('ut.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }

        $tuList = [];
        //0 - все 1 - погашение 2 - выдача
        if ($this->TypeUslug == 1 || $this->TypeUslug == 0) {
            $tuList = array_merge($tuList, TU::InAll());
        }
        if ($this->TypeUslug == 2 || $this->TypeUslug == 0) {
            $tuList = array_merge($tuList, TU::OutMfo());
        }

        $query->andWhere(['in', 'ut.IsCustom', $tuList]);
        $query->groupBy('ps.IdUsluga');
        $res = $query->cache(10)->all();

        $ret = [];
        foreach ($res as $row) {

            $row['VoznagSumm'] = $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];

            $indx = $row['IDPartner'];
            if (!isset($ret[$indx])) {
                $row['CntPays'] = 1;

                $typeVyvyod = 0;
                if (in_array($row['IsCustom'], [TU::$TOSCHET, TU::$TOCARD])) {
                    $typeVyvyod = 1;
                }

                $row['SummVyveden'] = Yii::$app->db->createCommand('
                    SELECT
                        SUM(`Summ`)
                    FROM
                        `vyvod_system`
                    WHERE
                        `IdPartner` = :IDPART
                        AND ((`DateFrom` >= :DATEFROM AND `DateTo` <= :DATETO)
                                 OR (`DateFrom` BETWEEN :DATEFROM AND :DATETO) 
                                 OR (`DateTo` BETWEEN :DATEFROM AND :DATETO)
                            )
                        AND `TypeVyvod` = :TYPEVYVOD
                        AND `SatateOp` IN (0,1)
                ', [':IDPART' => $row['IDPartner'], ':DATEFROM' => $dateFrom, ':DATETO' => $dateTo, ':TYPEVYVOD' => $typeVyvyod])->queryScalar();

                $row['DataVyveden'] = Yii::$app->db->createCommand('
                    SELECT
                        `DateTo`
                    FROM
                        `vyvod_system`
                    WHERE
                        `IdPartner` = :IDPART
                        AND `DateTo` <= :DATETO
                        AND `TypeVyvod` = :TYPEVYVOD
                        AND `SatateOp` IN (0,1)
                    ORDER BY `DateTo` DESC
                    LIMIT 1
                ', [':IDPART' => $row['IDPartner'], ':DATETO' => $dateTo, ':TYPEVYVOD' => $typeVyvyod])->queryScalar();

                if (!in_array($row['IsCustom'], [TU::$TOSCHET, TU::$TOCARD])) {
                    $row['SummPerechisl'] = Yii::$app->db->createCommand('
                        SELECT
                            SUM(`SumOp`)
                        FROM
                            `vyvod_reestr`
                        WHERE
                            `IdPartner` = :IDPART
                            AND ((`DateFrom` >= :DATEFROM AND `DateTo` <= :DATETO)
                                 OR (`DateFrom` BETWEEN :DATEFROM AND :DATETO) 
                                 OR (`DateTo` BETWEEN :DATEFROM AND :DATETO)
                            )
                            AND `StateOp` IN (0,1)
                    ', [':IDPART' => $row['IDPartner'], ':DATEFROM' => $dateFrom, ':DATETO' => $dateTo])->queryScalar();

                    $row['DataPerechisl'] = Yii::$app->db->createCommand('
                        SELECT
                            `DateTo`
                        FROM
                            `vyvod_reestr`
                        WHERE
                            `IdPartner` = :IDPART
                            AND `DateTo` <= :DATETO
                            AND `StateOp` IN (0,1)
                        ORDER BY `DateTo` DESC
                        LIMIT 1
                    ', [':IDPART' => $row['IDPartner'], ':DATETO' => $dateTo])->queryScalar();

                } else {
                    $row['SummPerechisl'] = 0;
                    $row['DataPerechisl'] = 0;
                }


                $ret[$indx] = $row;

            } else {
                $ret[$indx]['SummPay'] += $row['SummPay'];
                $ret[$indx]['ComissSumm'] += $row['ComissSumm'];
                $ret[$indx]['VoznagSumm'] += $row['VoznagSumm'];
                $ret[$indx]['MerchVozn'] += $row['MerchVozn'];
                $ret[$indx]['BankComis'] += $row['BankComis'];
                $ret[$indx]['CntPays'] += $row['CntPays'];
            }
        }

        return $ret;
    }

    /**
     * Сумма вознаграждения по партнёру
     * @return int|mixed
     * @throws \yii\db\Exception
     */
    public function GetSummVoznag()
    {
        $sumVoznag = 0;
        $otch = $this->GetOtchMerchant(true);
        foreach ($otch as $row) {
            $sumVoznag += $row['VoznagSumm'];
        }

        return $sumVoznag;
    }
}