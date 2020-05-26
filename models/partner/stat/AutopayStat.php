<?php

namespace app\models\partner\stat;

use app\models\partner\UserLk;
use app\models\TU;
use Yii;
use yii\base\Model;
use yii\db\Query;

class AutopayStat extends Model
{
    public $IdPart;
    public $datefrom;
    public $dateto;
    public $datetype = 0;

    public function rules()
    {
        return [
            [['IdPart', 'datetype'], 'integer'],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y'],
            [['datefrom', 'dateto'], 'required']
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    public function GetData($IsAdmin)
    {
        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom);
        $dateto = strtotime($this->dateto);

        $ret = [
            'cntcards' => 0,
            'cntnewcards' => 0,
            'activecards' => 0,
            'reqcards' => 0,
            'payscards' => 0,
            'sumpayscards ' => 0
        ];

        //видеть общее количество привязанных карт
        $query = new Query();
        $query
            ->select(['c.ID'])
            ->from('`cards` AS c')
            ->leftJoin('user AS u', 'u.ID = c.IdUser')
            ->where(['c.TypeCard' => 0]);
        if ($IdPart > 0) {
            $query->andWhere('u.ExtOrg = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $ret['cntcards'] = $query->count();

        //Количество новых карт
        $query = new Query();
        $query
            ->select(['c.ID'])
            ->from('`cards` AS c')
            ->leftJoin('user AS u', 'u.ID = c.IdUser')
            ->where(['c.TypeCard' => 0])
            ->andWhere(['between', 'c.DateAdd', $datefrom, $dateto]);
        if ($IdPart > 0) {
            $query->andWhere('u.ExtOrg = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $ret['cntnewcards'] = $query->count();

        //сколько активных привязанных карт
        $query = new Query();
        $query
            ->select(['c.ID'])
            ->from('`cards` AS c')
            ->leftJoin('`pay_schet` AS ps', 'ps.IdKard = c.ID')
            ->leftJoin('user AS u', 'u.ID = c.IdUser')
            ->where(['between', 'ps.DateCreate', $datefrom, $dateto])
            ->andWhere(['c.TypeCard' => 0])
            ->andWhere(['ps.IsAutoPay' => 1]);
        if ($IdPart > 0) {
            $query->andWhere('u.ExtOrg = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $query->groupBy('c.ID');
        $ret['activecards'] = $query->count();

        //количество успешных платежей
        $query = new Query();
        $query
            ->select(['ps.ID'])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`cards` AS c', 'ps.IdKard = c.ID')
            ->leftJoin('user AS u', 'u.ID = c.IdUser')
            ->where(['between', 'ps.DateCreate', $datefrom, $dateto])
            ->andWhere(['c.TypeCard' => 0])
            ->andWhere(['ps.IsAutoPay' => 1, 'ps.Status' => 1]);
        if ($IdPart > 0) {
            $query->andWhere('u.ExtOrg = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        if ($ret['activecards'] > 0) {
            $ret['reqcards'] = $query->count() / $ret['activecards'] / ceil(($dateto - $datefrom) / (60 * 60 * 24));
        }

        //Сколько успешных запросов
        $query = new Query();
        $query
            ->select(['ps.SummPay'])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`cards` AS c', 'ps.IdKard = c.ID')
            ->leftJoin('user AS u', 'u.ID = c.IdUser')
            ->where(['between', 'ps.DateCreate', $datefrom, $dateto])
            ->andWhere(['c.TypeCard' => 0])
            ->andWhere(['ps.IsAutoPay' => 1, 'ps.Status' => 1]);
        if ($IdPart > 0) {
            $query->andWhere('u.ExtOrg = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $ret['payscards'] = $query->count();
        $ret['sumpayscards'] = $query->sum('SummPay');

        return $ret;
    }

    /**
     * Рекуррентные платежи
     * @return array
     * @throws \Throwable
     */
    public function GetRecurrentData()
    {
        $IdPart = UserLk::IsAdmin(Yii::$app->user) ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

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
                ->select(['SummPay', 'ComissSumm', 'DateCreate'])
                ->from('`pay_schet` AS ps')
                ->leftJoin('`uslugatovar` AS qp', 'ps.IdUsluga = qp.ID')
                ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                    ':DATEFROM' => $datefrom, ':DATETO' => $dateto
                ])
                ->andWhere('ps.Status = 1 AND ps.IsAutoPay = 1')
                ->andWhere(['qp.IsCustom' => TU::AutoPay()]);

            if ($IdPart > 0) {
                $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
            }

            return $query->all();
        }, 60);

        foreach ($rows as $row) {
            if ($this->datetype == 0) {
                //График изменения суммы ежемесячных регулярных платежей
                $SummPay = $row['SummPay'] / 100.0;
                $DatePay = date('m.Y', $row['DateCreate']);
                if (isset($data[$DatePay])) {
                    $data[$DatePay][$ykey] += $SummPay;
                } else {
                    $data[$DatePay] = [$ykey => $SummPay, $xkey => $DatePay];
                }
            } elseif ($this->datetype == 1) {
                //График изменения средней выручки с одного пользователя в месяц
                $ComissSumm = $row['ComissSumm'] / 100.0;
                $DatePay = date('m.Y', $row['DateCreate']);
                if (isset($data[$DatePay])) {
                    $data[$DatePay]['sum'] += $ComissSumm;
                    $data[$DatePay]['cnt']++;
                } else {
                    $data[$DatePay] = ['sum' => $ComissSumm, 'cnt' => 1, $xkey => $DatePay];
                }

            } elseif ($this->datetype == 2) {
                //График ежемесячного оттока пользователей.
                //$SummPay = $row['SummPay']/100.0;
                $DatePay = date('m.Y', $row['DateCreate']);
                if (isset($data[$DatePay])) {
                    $data[$DatePay]['cnt']++;
                } else {
                    $data[$DatePay] = ['cnt' => 1, $xkey => $DatePay];
                }

            } elseif ($this->datetype == 3) {
                //График изменение средней суммы платежей, полученных с одного плательщика за весь период сотрудничества
                $SummPay = $row['SummPay'] / 100.0;
                $DatePay = date('m.Y', $row['DateCreate']);
                if (isset($data[$DatePay])) {
                    $data[$DatePay]['sum'] += $SummPay;
                    $data[$DatePay]['cnt']++;
                } else {
                    $data[$DatePay] = ['sum' => $SummPay, 'cnt' => 1, $xkey => $DatePay];
                }
            }
        }

        $dataJ = [];
        if (!empty($data)) {
            $prev = 0;
            foreach ($data as $rd) {
                if ($this->datetype == 0) {
                    $rd[$ykey] = round($rd[$ykey], 2);
                    $dataJ[] = $rd;
                } elseif ($this->datetype == 1) {
                    $rd[$ykey] = $rd['cnt'] > 0 ? round($rd['sum'] / $rd['cnt'], 2) : 0;
                    unset($rd['sum']);
                    unset($rd['cnt']);
                    $dataJ[] = $rd;
                } elseif ($this->datetype == 2) {
                    if ($prev) {
                        $rd[$ykey] = round(($rd['cnt'] - $prev) / $prev * 100, 2);
                        $prev = $rd['cnt'];
                        unset($rd['cnt']);
                        $dataJ[] = $rd;
                    } else {
                        $prev = $rd['cnt'];
                    }
                } elseif ($this->datetype == 3) {
                    $rd[$ykey] = $rd['cnt'] > 0 ? round($rd['sum'] / $rd['cnt'], 2) : 0;
                    unset($rd['sum']);
                    unset($rd['cnt']);
                    $dataJ[] = $rd;
                }
            }
        } else {
            $dataJ[] = [$xkey => 0, $ykey => 0];
        }

        $label = '';
        if ($this->datetype == 0) {
            $label = "Платежи";
        } elseif ($this->datetype == 1) {
            $label = "Средняя выручка";
        } elseif ($this->datetype == 2) {
            $label = "Процент оттока";
        } elseif ($this->datetype == 3) {
            $label = "Средний платеж";
        }

        return ['status' => 1, 'data' => $dataJ, 'label' => $label];
    }
}