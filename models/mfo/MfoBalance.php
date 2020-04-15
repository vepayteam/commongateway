<?php


namespace app\models\mfo;

use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\partner\admin\VoznagStat;
use app\models\partner\stat\ExportExcel;
use app\models\payonline\Partner;
use app\models\queue\ReceiveStatementsJob;
use app\models\TU;
use Yii;
use yii\db\Query;

class MfoBalance
{
    /** @var $Partner Partner */
    public $Partner;

    public function __construct(Partner $Partner)
    {
        $this->Partner = $Partner;
    }

    /**
     * Выписка по локальному счету
     * @param int $TypeAcc 0 - счет на выдачу 1 - счет на погашение 2 - номинальный счет
     * @param int $dateFrom
     * @param int $dateTo
     * @param int $sort
     * @return array
     * @throws \yii\db\Exception
     */
    public function GetOrdersLocal($TypeAcc, $dateFrom, $dateTo, $sort = 0)
    {
        $tabl = $TypeAcc == 0 ? 'partner_orderout' : 'partner_orderin';

        $query = (new Query())
            ->select('*')
            ->from($tabl)
            ->where(['IdPartner' => $this->Partner->ID])
            ->andWhere('DateOp BETWEEN :DATEFROM AND :DATETO', [':DATEFROM' => $dateFrom, ':DATETO' => $dateTo]);

        if ($sort == 0) {
            $query->orderBy(['ID' => SORT_DESC]);
        }

        return $query->all();
    }

    /**
     * Баланс МФО c ТКБ
     * @param bool $IsAdmin
     * @return array
     * @throws \yii\db\Exception
     */
    public function GetBalance($IsAdmin)
    {
        $ret = [];

        $bal = Yii::$app->cache->get('mfo_balance_'.$this->Partner->ID);
        if (!$bal && $IsAdmin) {

            $mfo = new MfoReq();
            $mfo->mfo = $this->Partner->ID;
            $TcbGate = new TcbGate($this->Partner->ID, TCBank::$AFTGATE);
            $tcBank = new TCBank($TcbGate);

            if (!empty($this->Partner->SchetTcbNominal)) {
                $bal = $tcBank->getBalanceAcc(['account' => $this->Partner->SchetTcbNominal]);
                if ($bal && $bal['status'] == 1) {
                    $ret['tcbnomin'] = $bal['amount'];
                }
            }
            if (!empty($this->Partner->SchetTcbTransit)) {
                $balTr = $tcBank->getBalanceAcc(['account' => $this->Partner->SchetTcbTransit]);
                if (!empty($this->Partner->SchetTcbTransit) && isset($balTr) && $balTr['status'] == 1) {
                    $ret['tcbtrans'] = $balTr['amount'];
                }
            }
            if (!empty($this->Partner->SchetTcb)) {
                $bal = $tcBank->getBalanceAcc(['account' => $this->Partner->SchetTcb]);
                if ($bal && $bal['status'] == 1) {
                    $ret['tcb'] = $bal['amount'];
                }
            } else {
                $bal = $tcBank->getBalance();
                if ($bal && $bal['status'] == 1) {
                    $ret['tcb'] = $bal['amount'];
                }
            }

            Yii::$app->cache->set('mfo_balance_'.$this->Partner->ID, $ret, 60);

        } else {
            $ret = $bal;
        }

        $ret['localin'] = $this->Partner->BalanceIn / 100.0 - $this->TodayPays() / 100.0;
        $ret['localout'] = $this->Partner->BalanceOut / 100.0;

        $comispogas = $this->GetComissPogas()/100.0;
        $comisvyd = $this->GetComissVyplat(false)/100.0;
        $comisvydtoday = $this->GetComissVyplat(true)/100.0;
        $ret['comisin'] = $comispogas;
        $ret['comisout'] = $comisvyd;

        if (!empty($this->Partner->SchetTcbNominal)) {
            //номинальный счет - вся комиссия на счете выдачи, и сегдняшнюю комиссию по выдаче не учитываем
            $ret['localout'] += $comisvydtoday;
        } else {
            //транзитный выдача
            if (!$this->Partner->VoznagVyplatDirect) {
                //комиссию за выдачу не списываем
                $ret['localout'] += $comisvyd;
            }
            if ($this->Partner->IsCommonSchetVydacha) {
                //комиссия по выдаче - со счета погашения
                $ret['localout'] -= $comispogas;
            }
        }

        return $ret;
    }

    /**
     * Выписка по счету МФО c ТКБ
     * @param int $TypeAcc 0 - счет на выдачу 1 - счет на погашение 2 - номинальный счет
     * @param int $dateFrom
     * @param int $dateTo
     * @param int $sort
     * @return array
     * @throws \yii\db\Exception
     */
    public function GetBankStatemets($TypeAcc, $dateFrom, $dateTo, $sort = 0)
    {
        $dates = Yii::$app->db->createCommand("
            SELECT
                `DateUpdateFrom`,
                `DateUpdateTo`
            FROM
                `statements_planner`
            WHERE
                `IdPartner` = :IDPARTNER
                AND `IdTypeAcc` = :ACCTYPE
        ", [':IDPARTNER' => $this->Partner->ID, ':ACCTYPE' => $TypeAcc])->queryOne();

        if (!$dates ||
            $dateFrom < $dates['DateUpdateFrom'] ||
            ($dateTo > $dates['DateUpdateTo'] && $dates['DateUpdateTo'] < time() - 60 * 15)
        ) {
            //обновить выписку (через очередь)
            $IdJob = Yii::$app->queue->push(new ReceiveStatementsJob([
                'IdPartner' => $this->Partner->ID,
                'TypeAcc' => $TypeAcc,
                'datefrom' => $dateFrom,
                'dateto' => $dateTo,
            ]));
            //Yii::$app->queue->run(false); //сразу выполнить
            //for ($i = 0; $i < 15 && !Yii::$app->queue->isDone($IdJob);$i++) sleep(1);
        }

        $ret = [];

        $desc = "DESC";
        if ($sort == 1) $desc = "";

        $result = Yii::$app->db->createCommand("
            SELECT
                `ID`,
                `IdPartner`,
                `TypeAccount`,
                `BnkId`,
                `NumberPP`,
                `DatePP`,
                `DateDoc`,
                `SummPP`,
                `SummComis`,
                `Description`,
                `IsCredit`,
                `Name`,
                `Inn`,
                `Account`,
                `Bic`,
                `Bank`,
                `BankAccount`
            FROM
                `statements_account`
            WHERE
                `IdPartner` = :IDPARTNER
                AND `DatePP` BETWEEN :DATEFROM AND :DATETO
                AND `TypeAccount` = :ACCTYPE
            ORDER BY `DatePP` ".$desc.", `ID` ".$desc."
        ", [':IDPARTNER' => $this->Partner->ID, ':DATEFROM' => $dateFrom, ':DATETO' => $dateTo, ':ACCTYPE' => $TypeAcc])->query();

        while ($row = $result->read()) {
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * Вознарждение по погашению
     * @return int
     * @throws \yii\db\Exception
     */
    private function GetComissPogas()
    {
        $datefrom = $this->GetPrevDateVoznVypl(0);
        if (!$datefrom) {
            $datefrom = mktime(0, 0, 0, date('n') - 1, 1, date('Y'));
        } else {
            $datefrom++;
        }
        $dateto = strtotime('today') - 1;
        $vs = new VoznagStat();

        $vs->setAttributes([
            'IdPart' => $this->Partner->ID,
            'datefrom' => date('d.m.Y H:i', $datefrom),
            'dateto' => date('d.m.Y H:i', $dateto),
            'TypeUslug' => 1
        ]);
        $VoznagSumm = 0;
        $otch = $vs->GetOtchMerchant(true);
        foreach ($otch as $row) {
            $VoznagSumm += $row['VoznagSumm'];
        }
        return $VoznagSumm;
    }

    /**
     * Вознарждение по выплатам
     * @param bool $todayComis
     * @return int
     * @throws \yii\db\Exception
     */
    private function GetComissVyplat($todayComis)
    {
        $vs = new VoznagStat();
        $VoznagSumm = 0;

        if ($todayComis) {
            //комиссия за текущий день плюс комиссия банка (списывают на следующий день по краутфандингу)
            $vs->setAttributes([
                'IdPart' => $this->Partner->ID,
                'datefrom' => date('d.m.Y H:i', strtotime('today')),
                'dateto' => date('d.m.Y H:i', time() - 1),
                'TypeUslug' => 2
            ]);
            $otch = $vs->GetOtchMerchant(true);
            foreach ($otch as $row) {
                $VoznagSumm += $row['MerchVozn'];
            }
        } else {
            //комиссия Vepay за месяц, за вычетом комиссии банка (VoznagSumm)
            $datefrom = $this->GetPrevDateVoznVypl(1);
            if (!$datefrom) {
                $datefrom = mktime(0, 0, 0, date('n') - 1, 1, date('Y'));
            } else {
                $datefrom++;
            }
            $dateto = time() - 1;

            $vs->setAttributes([
                'IdPart' => $this->Partner->ID,
                'datefrom' => date('d.m.Y H:i', $datefrom),
                'dateto' => date('d.m.Y H:i', $dateto),
                'TypeUslug' => 2
            ]);
            $otch = $vs->GetOtchMerchant(true);
            foreach ($otch as $row) {
                $VoznagSumm += $row['VoznagSumm'];
            }
        }

        return $VoznagSumm;
    }

    /**
     * @param int $TypeVyvod 0 - pogashenie 1 - vyplaty
     * @return false|string|null
     * @throws \yii\db\Exception
     */
    public function GetPrevDateVoznVypl($TypeVyvod)
    {
        $PrevDateTo = Yii::$app->db->createCommand("
            SELECT
                `DateTo`
            FROM
                `vyvod_system`
            WHERE
                `IdPartner` = :IDMFO
                AND `TypeVyvod` = :TYPEVYVOD
            ORDER BY `DateTo` DESC 
            LIMIT 1
        ", [':IDMFO' => $this->Partner->ID, ':TYPEVYVOD' => $TypeVyvod])->queryScalar();

        return $PrevDateTo;
    }

    private function TodayPays()
    {
        $vs = new VoznagStat();

        $vs->setAttributes([
            'IdPart' => $this->Partner->ID,
            'datefrom' => date('d.m.Y H:i', strtotime('today')),
            'dateto' => date('d.m.Y H:i'),
            'TypeUslug' => 1
        ]);
        $VoznagSumm = 0;
        $otch = $vs->GetOtchMerchant(true);
        foreach ($otch as $row) {
            $VoznagSumm += $row['SummPay'];
            $VoznagSumm -= $row['MerchVozn'];
        }

        return $VoznagSumm;
    }

    /**
     * Остаток на начало
     *
     * @param int $date
     * @param int $TypeAcc
     * @return int
     * @throws \yii\db\Exception
     */
    public function GetOstBeg($date, $dateTo, $TypeAcc)
    {
        $tabl = $TypeAcc == 0 ? 'partner_orderout' : 'partner_orderin';

        $query = (new Query())
            ->select('SummAfter')
            ->from($tabl)
            ->where(['IdPartner' => $this->Partner->ID])
            ->andWhere('DateOp < :DATEFROM', [':DATEFROM' => $date])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1);

        $ost = $query->scalar();

        $MerchVozn = 0;
        $datefrom = Yii::$app->db->createCommand("
            SELECT
                `DateTo`
            FROM
                `vyvod_system`
            WHERE
                `IdPartner` = :IDMFO
                AND `TypeVyvod` = :TYPEVYVOD
                AND `DateOp` < :DATETO
            ORDER BY `ID` DESC
            LIMIT 1
        ", [':IDMFO' => $this->Partner->ID, ':TYPEVYVOD' => $TypeAcc == 0 ? 1 : 0, ':DATETO' => $date])->queryScalar();

        $vs = new VoznagStat();
        $vs->setAttributes([
            'IdPart' => $this->Partner->ID,
            'datefrom' => date('d.m.Y H:i', $datefrom),
            'dateto' => date('d.m.Y H:i', $date),
            'TypeUslug' => $TypeAcc
        ]);
        $otch = $vs->GetOtchMerchant(true);
        foreach ($otch as $row) {
            $MerchVozn += $row['MerchVozn'] - $row['BankComis'];
        }

        return $ost + $MerchVozn;
    }

    /**
     * Остаток на конец
     *
     * @param int $date
     * @param int $TypeAcc
     * @return int
     * @throws \yii\db\Exception
     */
    public function GetOstEnd($date, $TypeAcc)
    {
        $tabl = $TypeAcc == 0 ? 'partner_orderout' : 'partner_orderin';

        $query = (new Query())
            ->select('SummAfter')
            ->from($tabl)
            ->where(['IdPartner' => $this->Partner->ID])
            ->andWhere('DateOp <= :DATETO', [':DATETO' => $date])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1);

        $ost = $query->scalar();

        $MerchVozn = 0;
        $datefrom = Yii::$app->db->createCommand("
            SELECT
                `DateTo`
            FROM
                `vyvod_system`
            WHERE
                `IdPartner` = :IDMFO
                AND `TypeVyvod` = :TYPEVYVOD
                AND `DateOp` < :DATETO
            ORDER BY `ID` DESC
            LIMIT 1
        ", [':IDMFO' => $this->Partner->ID, ':TYPEVYVOD' => $TypeAcc == 0 ? 1 : 0, ':DATETO' => $date])->queryScalar();

        $vs = new VoznagStat();
        $vs->setAttributes([
            'IdPart' => $this->Partner->ID,
            'datefrom' => date('d.m.Y H:i', $datefrom),
            'dateto' => date('d.m.Y H:i', $date),
            'TypeUslug' => $TypeAcc
        ]);
        $otch = $vs->GetOtchMerchant(true);
        foreach ($otch as $row) {
            $MerchVozn += $row['MerchVozn'] - $row['BankComis'];
        }

        return $ost + $MerchVozn;
    }

    /**
     * Экспорт выписки
     * @param array $get
     * @return bool|false|string
     * @throws \yii\db\Exception
     */
    public function ExportVyp(array $get)
    {
        $TypeAcc = (int)($get['istransit'] ?? 0);
        $dateFrom = (int)($get['dateFrom'] ?? 0);
        $dateTo = (int)($get['dateTo'] ?? 0);
        $stlst = $this->GetBankStatemets($TypeAcc, $dateFrom, $dateTo);

        $head = ['Дата', 'Дата документа', 'Сумма поступления', 'Сумма списания', 'Комментарий', 'Контрагент', 'Инн'];
        $sizes = [20, 20, 15, 15, 50, 50, 10];
        $itogs = [2 => 1, 3 => 1];

        $data = [];
        foreach ($stlst as $row) {
            $data[] = [
                date("d.m.Y H:i:s", $row['DatePP']),
                date("d.m.Y H:i:s", $row['DateDoc']),
                $row['IsCredit'] == 1 ? ($row['SummPP'] + $row['SummComis']) / 100.0 : 0,
                $row['IsCredit'] == 0 ? ($row['SummPP'] + $row['SummComis']) / 100.0 : 0,
                $row['Description'],
                $row['Name'],
                $row['Inn']
            ];
        }

        $ExportExcel = new ExportExcel();
        return $ExportExcel->CreateXls("Экспорт", $head, $data, $sizes, $itogs);

    }

}