<?php

namespace app\models\partner\stat;

use app\models\partner\UserLk;
use app\models\TU;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\VarDumper;

class StatGraph extends Model
{
    public $datefrom = 0;
    public $dateto = 0;
    public $datetype = 0;
    public $partner = 0;
    public $usluga = [];
    public $TypeUslug = [];
    public $page;

    const SCENARIO_DAY = 'day';
    const SCENARIO_MONTH = 'month';
    const PAGE_ITEMS_COUNT = 1000;

    public function rules()
    {
        return [
            [['partner', 'datetype', 'page'], 'integer'],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y', 'on' => self::SCENARIO_DEFAULT],
            [['datefrom', 'dateto'], 'required', 'on' => self::SCENARIO_DEFAULT],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:m.Y', 'on' => self::SCENARIO_MONTH],
            [['datefrom', 'dateto'], 'required', 'on' => self::SCENARIO_MONTH],
            [['usluga', 'TypeUslug'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * Преобразуем данные для работы с бд.
    */
    public function afterValidate()
    {
        foreach ($this->usluga as $key => $item) {
            $this->usluga[$key] = (int)$item;
        }
        foreach ($this->TypeUslug as $key => $item) {
            $this->TypeUslug[$key] = (int)$item;
        }
        parent::afterValidate(); // TODO: Change the autogenerated stub
    }

    public function attributeLabels()
    {
        return [
            'datefrom' => 'Период',
            'dateto' => 'Период',
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function GetSale()
    {
        $IdPart = UserLk::IsAdmin(Yii::$app->user) ? $this->partner : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom . " 00:00:00");
        $dateto = strtotime($this->dateto . " 23:59:59");
        if ($datefrom < $dateto - 365 * 86400) {
            $datefrom = $dateto - 365 * 86400;
        }

        $data = [];
        $xkey = 'x';
        $ykey = 'a';

        $rows = Yii::$app->db->cache(function () use ($IdPart, $datefrom, $dateto) {

            $query = new Query();
            $query
                ->select(['SummPay', 'DateCreate'])
                ->from('`pay_schet` AS ps')
                ->leftJoin('`uslugatovar` AS qp', 'ps.IdUsluga = qp.ID')
                ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                    ':DATEFROM' => $datefrom, ':DATETO' => $dateto
                ])
                ->andWhere('ps.Status = 1');

            if ($IdPart > 0) {
                $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
            }
            if (count($this->usluga) > 0) {
                $query->andWhere(['in', 'qp.ID', $this->usluga]);
            }
            if (count($this->TypeUslug) > 0) {
                $query->andWhere(['in', 'qp.IsCustom', $this->TypeUslug]);
            } else {
                $query->andWhere(['in', 'qp.IsCustom', TU::InAll()]);
            }

            return $query->all();
        }, 60);

        foreach ($rows as $row) {
            $SummPay = $row['SummPay'] / 100.0;
            if ($this->datetype == 1) {
                $DatePay = date('m.Y', $row['DateCreate']);
            } else {
                $DatePay = date('d.m.Y', $row['DateCreate']);
            }
            if (isset($data[$DatePay])) {
                $data[$DatePay][$ykey] += $SummPay;
            } else {
                $data[$DatePay] = [$ykey => $SummPay, $xkey => $DatePay];
            }
        }

        $dataJ = [];
        if (!empty($data)) {
            foreach ($data as $rd) {
                $rd[$ykey] = round($rd[$ykey], 2);
                $dataJ[] = $rd;
            }
        } else {
            $dataJ[] = [$xkey => 0, $ykey => 0];
        }

        return ['status' => 1, 'data' => $dataJ];
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function GetSaleDraft()
    {
        $IdPart = UserLk::IsAdmin(Yii::$app->user) ? $this->partner : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom . " 00:00:00");
        $dateto = strtotime($this->dateto . " 23:59:59");
        if ($datefrom < $dateto - 365 * 86400) {
            $datefrom = $dateto - 365 * 86400;
        }

        $data = [];
        $xkey = 'x';
        $ykey = 'a';

        $rows = Yii::$app->db->cache(function () use ($IdPart, $datefrom, $dateto) {

            $query = new Query();
            $query
                ->select(['SummPay', 'DateCreate'])
                ->from('`pay_schet` AS ps')
                ->leftJoin('`uslugatovar` AS qp', 'ps.IdUsluga = qp.ID')
                ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                    ':DATEFROM' => $datefrom, ':DATETO' => $dateto
                ])
                ->andWhere('ps.Status = 1');

            if ($IdPart > 0) {
                $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
            }
            if (count($this->usluga) > 0) {
                $query->andWhere(['in', 'qp.ID', $this->usluga]);
            }
            if (count($this->TypeUslug) > 0) {
                $query->andWhere(['in', 'qp.IsCustom', $this->TypeUslug]);
            } else {
                $query->andWhere(['in', 'qp.IsCustom', TU::InAll()]);
            }

            return $query->all();
        }, 60);

        foreach ($rows as $row) {
            $SummPay = $row['SummPay'] / 100.0;
            if ($this->datetype == 1) {
                $DatePay = date('m.Y', $row['DateCreate']);
            } else {
                $DatePay = date('d.m.Y', $row['DateCreate']);
            }
            if (isset($data[$DatePay])) {
                $data[$DatePay][$ykey] += $SummPay;
                $data[$DatePay]['cnt']++;
            } else {
                $data[$DatePay] = [$ykey => $SummPay, $xkey => $DatePay, 'cnt' => 1];
            }
        }

        $dataJ = [];
        if (!empty($data)) {
            foreach ($data as $rd) {
                $rd[$ykey] = round($rd[$ykey] / $rd['cnt'], 2);
                unset($rd['cnt']);
                $dataJ[] = $rd;
            }
        } else {
            $dataJ[] = [$xkey => 0, $ykey => 0];
        }


        return ['status' => 1, 'data' => $dataJ];
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function GetSaleKonvers()
    {
        $IdPart = UserLk::IsAdmin(Yii::$app->user) ? $this->partner : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom . " 00:00:00");
        $dateto = strtotime($this->dateto . " 23:59:59");
        if ($datefrom < $dateto - 365 * 86400) {
            $datefrom = $dateto - 365 * 86400;
        }

        $data = [];
        $xkey = 'x';
        $ykey = 'a';

        $result = $this->GetSaleKonversQuery($IdPart, $datefrom, $dateto, $this->page);
        if(!is_array($result)) {
            return false;
        }

        foreach ($result['rows'] as $row) {
            if ($this->datetype == 1) {
                $DatePay = date('m.Y', $row['DateCreate']);
            } else {
                $DatePay = date('d.m.Y', $row['DateCreate']);
            }
            if (isset($data[$DatePay])) {
                if ($row['Status'] == 1) {
                    $data[$DatePay]['cntok']++;
                } else {
                    $data[$DatePay]['cnterr']++;
                }
            } else {
                $data[$DatePay] = [$xkey => $DatePay, 'cntok' => $row['Status'] == 1 ? 1 : 0, 'cnterr' => $row['Status'] != 1 ? 1 : 0];
            }
        }

        $dataJ = [];
        if (!empty($data)) {
            foreach ($data as $rd) {
                $rd[$ykey] = round($rd['cntok'] / ($rd['cnterr'] + $rd['cntok']) * 100.0, 2);
                unset($rd['cntok']);
                unset($rd['cnterr']);
                $dataJ[] = $rd;
            }
        } else {
            $dataJ[] = [$xkey => 0, $ykey => 0];
        }

        return ['status' => 1, 'number_pages' => $result['number_pages'], 'page' => $this->page, 'data' => $dataJ];
    }

    private function GetSaleKonversQuery($IdPart, $datefrom, $dateto, $page)
    {
        return Yii::$app->db->cache(function () use ($IdPart, $datefrom, $dateto, $page) {
            $query = new Query();
            $query
                ->select(['SummPay', 'DateCreate', 'Status'])
                ->from('`pay_schet` AS ps')
                ->leftJoin('`uslugatovar` AS qp', 'ps.IdUsluga = qp.ID')
                ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                    ':DATEFROM' => $datefrom, ':DATETO' => $dateto
                ]);
            if ($IdPart > 0) {
                $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
            }
            if (count($this->usluga) > 0) {
                $query->andWhere(['in', 'qp.ID', $this->usluga]);
            }
            if (count($this->TypeUslug) > 0) {
                $query->andWhere(['in', 'qp.IsCustom', $this->TypeUslug]);
            } else {
                $query->andWhere(['in', 'qp.IsCustom', TU::InAll()]);
            }
            $count = $query->count();
            $result['number_pages'] = $count > self::PAGE_ITEMS_COUNT ? floor($count / self::PAGE_ITEMS_COUNT) : 1;
            $query->limit(self::PAGE_ITEMS_COUNT);
            $query->offset($page * self::PAGE_ITEMS_COUNT);
            $result['rows'] = $query->all();
            return $result;
        }, 60);
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function GetPlatelshikData()
    {
        $IdPart = UserLk::IsAdmin(Yii::$app->user) ? $this->partner : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom . " 00:00:00");
        $dateto = strtotime($this->dateto . " 23:59:59");
        if ($datefrom < $dateto - 365 * 86400) {
            $datefrom = $dateto - 365 * 86400;
        }

        $rows = Yii::$app->db->cache(function () use ($IdPart, $datefrom, $dateto) {

            $query = new Query();
            $query
                ->select(['ps.ID', 'CardType', 'BankName', 'CountryUser', 'CityUser'])
                ->from('`pay_schet` AS ps')
                ->leftJoin('`uslugatovar` AS qp', 'ps.IdUsluga = qp.ID')
                ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                    ':DATEFROM' => $datefrom, ':DATETO' => $dateto
                ])
                ->andWhere('ps.Status = 1');

            if ($IdPart > 0) {
                $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
            }
            if (count($this->usluga) > 0) {
                $query->andWhere(['in', 'qp.ID', $this->usluga]);
            }
            if (count($this->TypeUslug) > 0) {
                $query->andWhere(['in', 'qp.IsCustom', $this->TypeUslug]);
            } else {
                $query->andWhere(['in', 'qp.IsCustom', TU::InAll()]);
            }

            return $query->all();
        }, 60);

        $countryJ = $cityJ = $bankJ = $cardJ = [];
        $country = $city = $bank = $card = [];
        foreach ($rows as $row) {
            if (isset($country[$row['CountryUser']])) {
                $country[$row['CountryUser']]++;
            } else {
                $country[$row['CountryUser']] = 1;
            }
            if (isset($city[$row['CityUser']])) {
                $city[$row['CityUser']]++;
            } else {
                $city[$row['CityUser']] = 1;
            }
            if (isset($card[$row['CardType']])) {
                $card[$row['CardType']]++;
            } else {
                $card[$row['CardType']] = 1;
            }
            if (isset($bank[$row['BankName']])) {
                $bank[$row['BankName']]++;
            } else {
                $bank[$row['BankName']] = 1;
            }
        }
        if (empty($country) && empty($city) && empty($card) && empty($bank)) {
            return ['status' => 0, 'message' => 'Нет данных для отображения'];
        }

        if (!empty($country)) {
            foreach ($country as $cnm => $ctr) {
                if (empty($cnm)) {
                    $cnm = 'n/a';
                }
                $countryJ[] = ['label' => $cnm, 'value' => $ctr];
            }
        } else {
            $countryJ = ['label' => '', 'value' => 0];

        }
        if (!empty($city)) {
            foreach ($city as $cnm => $ctr) {
                if (empty($cnm)) {
                    $cnm = 'n/a';
                }
                $cityJ[] = ['label' => $cnm, 'value' => $ctr];
            }
        } else {
            $cityJ = ['label' => '', 'value' => 0];
        }
        if (!empty($card)) {
            foreach ($card as $cnm => $ctr) {
                if (empty($cnm)) {
                    $cnm = 'n/a';
                }
                $cardJ[] = ['label' => $cnm, 'value' => $ctr];
            }
        } else {
            $cardJ = ['label' => '', 'value' => 0];
        }
        if (!empty($bank)) {
            foreach ($bank as $cnm => $ctr) {
                if (empty($cnm)) {
                    $cnm = 'n/a';
                }
                $bankJ[] = ['label' => $cnm, 'value' => $ctr];
            }
        } else {
            $bankJ = ['label' => '', 'value' => 0];
        }

        return ['status' => 1, 'country' => $countryJ, 'city' => $cityJ, 'card' => $cardJ, 'bank' => $bankJ];
    }

}