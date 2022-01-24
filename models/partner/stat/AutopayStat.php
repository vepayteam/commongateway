<?php

namespace app\models\partner\stat;

use app\models\ModelHelper;
use app\models\partner\UserLk;
use app\models\payonline\Cards;
use app\services\payment\models\PaySchet;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use Yii;
use yii\base\Model;
use yii\db\Query;

class AutopayStat extends Model
{
    use ModelHelper;

    public $IdPart;
    public $datefrom;
    public $dateto;
    public $datetype = 0;
    public $graphtype = 0;

    public function rules()
    {
        return [
            [['IdPart', 'datetype', 'graphtype'], 'integer'],
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

    /**
     * @param $isAdmin
     *
     * @return int[]
     */
    public function getData($isAdmin = null): array
    {
        $IdPart = ($this->isAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user));
        $idPart = $IdPart > 0 ? $IdPart : null;

        $datefrom = strtotime($this->datefrom . ' 00:00:00');
        $dateto = strtotime($this->dateto . ' 23:59:59');

        $ret = [
            'cntnewcards' => 0,
            'activecards' => 0,
            'reqonecard' => 0,
            'reqcards' => 0,
            'payscards' => 0,
            'sumpayscards ' => 0
        ];

        //Количество новых карт
        $queryPayShet = PaySchet::find()
            ->orWhere(['IdUsluga' => 1])
            ->orWhere(['RegisterCard' => 1])
            ->andWhere(['!=', 'Bank', '0'])
            ->andWhere(['Status' => [1,3]])
            ->andWhere(['between', 'DateCreate', $datefrom, $dateto])
            ->andFilterWhere(['IDPartner' => $idPart]);

        $ret['cntnewcards'] = $queryPayShet->count();

        //сколько активных привязанных карт
        $queryAciveCards = Uslugatovar::find()
            ->joinWith('cards')
            ->andWhere(['uslugatovar.iscustom' => TU::AutoPay()])
            ->andWhere(['cards.Typecard' => 0])
            ->andWhere(['between', 'DateCreate', $datefrom, $dateto])
            ->andFilterWhere(['IDPartner' => $idPart]);

        $ret['activecards'] = $queryAciveCards->count('DISTINCT `cards`.`ID`');

        //Количество запросов на одну карту
        $queryPayShet = PaySchet::find()
            ->joinWith(['cards', 'uslugatovar'])
            ->andWhere(['cards.TypeCard' => 0])
            ->andWhere(['uslugatovar.IsCustom' => TU::AutoPay()])
            ->andWhere(['between', 'DateCreate', $datefrom, $dateto])
            ->andFilterWhere(['IDPartner' => $idPart]);

        $ret['reqcards'] = $queryPayShet->count();

        if ($ret['activecards'] > 0) {
            $ret['reqonecard'] = $ret['reqcards'] / $ret['activecards'] / ceil(($dateto + 1 - $datefrom) / (60 * 60 * 24));
        }

        //Сколько успешных запросов
        $querySuccess = PaySchet::find()
            ->joinWith(['cards', 'uslugatovar'])
            ->andWhere(['cards.TypeCard' => 0])
            ->andWhere(['pay_schet.Status' => 1])
            ->andWhere(['uslugatovar.IsCustom' => TU::AutoPay()])
            ->andWhere(['between', 'DateCreate', $datefrom, $dateto])
            ->andFilterWhere(['IDPartner' => $idPart]);

        $ret['payscards'] = $querySuccess->count();
        $ret['sumpayscards'] = $querySuccess->sum('SummPay');

        return $ret;
    }

    /**
     * Рекуррентные платежи
     * @return array
     * @throws \Throwable
     * @deprecated
     */
    public function GetRecurrentData()
    {
        $IdPart = UserLk::IsAdmin(Yii::$app->user) ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom . " 00:00:00");
        $dateto = strtotime($this->dateto . " 23:59:59");
        if ($datefrom < $dateto - 365 * 86400) {
            $datefrom = $dateto - 365 * 86400 - 86399;
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
                    ':DATEFROM' => $datefrom,
                    ':DATETO' => $dateto
                ])
                ->andWhere('ps.Status = 1 AND ps.IsAutoPay = 1')
                ->andWhere(['qp.IsCustom' => TU::AutoPay()]);

            if ($IdPart > 0) {
                $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
            }

            return $query->all();
        }, 60);

        $groupDate = 'd.m.Y';
        if ($this->datetype == 1) {
            $groupDate = 'm.Y';
        }

        foreach ($rows as $row) {
            if ($this->graphtype == 0) {
                //График изменения суммы ежемесячных регулярных платежей
                $SummPay = $row['SummPay'] / 100.0;
                $DatePay = date($groupDate, $row['DateCreate']);
                if (isset($data[$DatePay])) {
                    $data[$DatePay][$ykey] += $SummPay;
                } else {
                    $data[$DatePay] = [$ykey => $SummPay, $xkey => $DatePay];
                }
            } elseif ($this->graphtype == 1) {
                //График изменения средней выручки с одного пользователя в месяц
                $ComissSumm = $row['ComissSumm'] / 100.0;
                $DatePay = date($groupDate, $row['DateCreate']);
                if (isset($data[$DatePay])) {
                    $data[$DatePay]['sum'] += $ComissSumm;
                    $data[$DatePay]['cnt']++;
                } else {
                    $data[$DatePay] = ['sum' => $ComissSumm, 'cnt' => 1, $xkey => $DatePay];
                }

            } elseif ($this->graphtype == 2) {
                //График изменение средней суммы платежей, полученных с одного плательщика за весь период сотрудничества
                $SummPay = $row['SummPay'] / 100.0;
                $DatePay = date($groupDate, $row['DateCreate']);
                if (isset($data[$DatePay])) {
                    $data[$DatePay]['sum'] += $SummPay;
                    $data[$DatePay]['cnt']++;
                } else {
                    $data[$DatePay] = ['sum' => $SummPay, 'cnt' => 1, $xkey => $DatePay];
                }
            } elseif ($this->graphtype == 3) {
                //График ежемесячного оттока пользователей.
                //$SummPay = $row['SummPay']/100.0;
                $DatePay = date($groupDate, $row['DateCreate']);
                if (isset($data[$DatePay])) {
                    $data[$DatePay]['cnt']++;
                } else {
                    $data[$DatePay] = ['cnt' => 1, $xkey => $DatePay];
                }
            }
        }

        $dataJ = [];
        if (!empty($data)) {
            $prev = 0;
            foreach ($data as $rd) {
                if ($this->graphtype == 0) {
                    $rd[$ykey] = round($rd[$ykey], 2);
                    $dataJ[] = $rd;
                } elseif ($this->graphtype == 1) {
                    $rd[$ykey] = $rd['cnt'] > 0 ? round($rd['sum'] / $rd['cnt'], 2) : 0;
                    unset($rd['sum']);
                    unset($rd['cnt']);
                    $dataJ[] = $rd;
                } elseif ($this->graphtype == 2) {
                    $rd[$ykey] = $rd['cnt'] > 0 ? round($rd['sum'] / $rd['cnt'], 2) : 0;
                    unset($rd['sum']);
                    unset($rd['cnt']);
                    $dataJ[] = $rd;
                } elseif ($this->graphtype == 3) {
                    if ($prev) {
                        $rd[$ykey] = round(($rd['cnt'] - $prev) / $prev * 100, 2);
                        $prev = $rd['cnt'];
                        unset($rd['cnt']);
                        $dataJ[] = $rd;
                    } else {
                        $prev = $rd['cnt'];
                    }
                }
            }
        } else {
            $dataJ[] = [$xkey => 0, $ykey => 0];
        }

        $label = '';
        if ($this->graphtype == 0) {
            $label = "Платежи";
        } elseif ($this->graphtype == 1) {
            $label = "Средняя выручка";
        } elseif ($this->graphtype == 2) {
            $label = "Средний платеж";
        } elseif ($this->graphtype == 3) {
            $label = "Процент оттока";
        }

        return ['status' => 1, 'data' => $dataJ, 'label' => $label];
    }
}