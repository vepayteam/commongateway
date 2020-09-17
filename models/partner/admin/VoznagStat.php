<?php


namespace app\models\partner\admin;


use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use Carbon\Carbon;
use Yii;
use yii\base\Model;
use yii\caching\TagDependency;
use yii\db\Query;

class VoznagStat extends Model
{
    const STAT_DAY_CACHE_PREFIX = 'VoznagStat_ForDay_';
    const STAT_DAY_TAG_PREFIX = 'VoznagStat_ForDay_';

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

        $partner = Partner::findOne(['ID' => $IdPart]);

        $result = [];
        $tuList = [];
        //0 - все 1 - погашение 2 - выдача
        if ($this->TypeUslug == 1 || $this->TypeUslug == 0) {
            $tuList = array_merge($tuList, TU::InAll());
        }
        if ($this->TypeUslug == 2 || $this->TypeUslug == 0) {
            $tuList = array_merge($tuList, TU::OutMfo());
        }
        $uslugatovars = $partner->getUslugatovars()
            ->andWhere(['in', 'IsCustom', $tuList])
            ->all();

        foreach ($uslugatovars as $uslugatovar) {
            $data = $this->iterUslugatovar($uslugatovar);
            $result[] = [
                'NamePartner' => $partner->Name,
                'IDPartner' => $partner->ID,

                'SummPay' => $data['SummPay'],
                'ComissSumm' => $data['ComissSumm'],
                'MerchVozn' => $data['MerchVozn'],
                'BankComis' => $data['BankComis'],
                'CntPays' => $data['CntPays'],

                'IdUsluga' => $uslugatovar->ID,
                'IsCustom' => $uslugatovar->IsCustom,
                'ProvVoznagPC' => $uslugatovar->ProvVoznagPC,
                'ProvVoznagMin' => $uslugatovar->ProvVoznagMin,
                'ProvComisPC' => $uslugatovar->ProvComisPC,
                'ProvComisMin' => $uslugatovar->ProvComisMin,
                'VoznagVyplatDirect' => $partner->VoznagVyplatDirect,
            ];
        }

        $dateFrom = strtotime($this->datefrom.":00");
        $dateTo = strtotime($this->dateto.":59");
        $ret = [];
        foreach ($result as $row) {
            $row['VoznagSumm'] = $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];

            $indx = $row['IDPartner'];
            if (!isset($ret[$indx])) {
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
                ', [':IDPART' => $row['IDPartner'], ':DATEFROM' => $dateFrom, ':DATETO' => $dateTo, ':TYPEVYVOD' => $typeVyvyod])
                    ->cache(60*60)
                    ->queryScalar();

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
                ', [':IDPART' => $row['IDPartner'], ':DATETO' => $dateTo, ':TYPEVYVOD' => $typeVyvyod])
                    ->cache(60*60)
                    ->queryScalar();

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
                    ', [':IDPART' => $row['IDPartner'], ':DATEFROM' => $dateFrom, ':DATETO' => $dateTo])
                        ->cache(60*60)
                        ->queryScalar();

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
                    ', [':IDPART' => $row['IDPartner'], ':DATETO' => $dateTo])
                        ->cache(60*60)
                        ->queryScalar();

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

    private function iterUslugatovar(Uslugatovar $uslugatovar)
    {
        $result = [
            'SummPay' => 0,
            'ComissSumm' => 0,
            'MerchVozn' => 0,
            'BankComis' => 0,
            'CntPays' => 0,
        ];
        $dateFrom = Carbon::createFromFormat('d.m.Y H:i:s', $this->datefrom.":00");
        $dateTo = Carbon::createFromFormat('d.m.Y H:i:s', $this->dateto.":59");

        $dateIterFrom = $dateFrom;
        $dateIterTo = $dateFrom->clone()->addDays(1);
        $isLastIter = false;
        while(True) {
            $cacheKey = self::STAT_DAY_CACHE_PREFIX
                . $uslugatovar->ID . '|'
                . $dateIterFrom->timestamp . '|'
                . $dateIterTo->timestamp;

            // Если дата итерации больше параметра, значит считаем текущий день
            // Значит следует привести, посчитать и завершить функцию
            if($dateIterTo->timestamp >= $dateTo->timestamp) {
                $isLastIter = true;
                $dateIterTo = $dateTo;
            }

            // Если считаем текущий или предыдущий день, не используем кэш
            if($dateIterTo->timestamp >= $dateTo->clone()->startOfDay()->addDays(-1)->timestamp) {
                $data = $this->iterDay($uslugatovar, $dateIterFrom, $dateIterTo);
            } else {
                $data = Yii::$app->cache->getOrSet($cacheKey, function() use ($uslugatovar, $dateIterFrom, $dateIterTo) {
                    return $this->iterDay($uslugatovar, $dateIterFrom, $dateIterTo);
                }, 60*60*24*30, new TagDependency(['tags' => self::STAT_DAY_TAG_PREFIX . $uslugatovar->ID]));
            }

            foreach ($data as $k => $v) {
                $result[$k] += $v;
            }

            if($isLastIter) {
                break;
            }
            $dateIterFrom->addDays(1);
            $dateIterTo->addDays(1);
        }

        return $result;
    }

    private function iterDay(Uslugatovar $uslugatovar, Carbon $dateFrom, Carbon $dateTo)
    {
        $query = new Query();
        $query
            ->select([
                'SUM(ps.SummPay) AS SummPay',
                'SUM(ps.ComissSumm) AS ComissSumm',
                'SUM(ps.MerchVozn) AS MerchVozn',
                'SUM(ps.BankComis) AS BankComis',
                'COUNT(*) AS CntPays',
            ])
            ->from('`pay_schet` AS ps FORCE INDEX(DateCreate_idx)')
            ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => $dateFrom->timestamp,
                ':DATETO' => $dateTo->timestamp
            ])
            ->andWhere('ps.IdUsluga = ' . $uslugatovar->ID)
            ->andWhere('ps.Status = 1');

        $result = $query->one();
        return $result;
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
