<?php


namespace app\models\planner;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\Options;
use app\models\payonline\BalancePartner;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\payonline\Provparams;
use app\models\payonline\Uslugatovar;
use app\models\Payschets;
use app\models\SendEmail;
use app\models\TU;
use Yii;

class VyvodSumPay
{
    /**
     * Вывод платежей МФО на их счет
     * (в рабочие дни, и в пн за пт+сб+вс)
     *
     * @throws \yii\db\Exception
     */
    public function execute()
    {
        $PBKPOrg = 1;

        if ($this->IsDisabledDay()) {
            //в праздничные дни не выгружать
            Yii::warning("VyvodSumPay: disabled day", "rsbcron");
            echo "VyvodSumPay: disabled day" . "\r\n";
            return;
        }

        $wd = date('w');
        if (in_array($wd, [2, 3, 4, 5])) {
            //вт-пт - за предыдущий
            $dateFromNeed = strtotime('yesterday');
        } elseif ($wd == 1) {
            //пн - за три дня
            $dateFromNeed = strtotime('last friday');
        } elseif (in_array($wd, [0, 6])) {
            Yii::warning("VyvodSumPay: weekends", "rsbcron");
            echo "VyvodSumPay: weekends" . "\r\n";
            return;
        }
        $dateTo = strtotime('today') - 1;

        Yii::warning("VyvodSumPay: from " . date('d.m.Y H:i:s', $dateFromNeed)."(".$dateFromNeed.") to " . date('d.m.Y H:i:s', $dateTo)."(".$dateTo.")", "rsbcron");
        echo "VyvodSumPay: from " . date('d.m.Y H:i:s', $dateFromNeed)."(".$dateFromNeed.") to " . date('d.m.Y H:i:s', $dateTo)."(".$dateTo.")" . "\r\n";

        $res = Yii::$app->db->createCommand('
            SELECT
                p.`ID`,
                p.`LoginTkbVyvod`,
                p.`LoginTkbPerevod`,
                r.NamePoluchat,
                r.INNPolushat,
                r.KPPPoluchat,
                r.BIKPoluchat,
                r.RaschShetPolushat,
                r.NaznachenPlatez,
                p.IsAutoPerevodToVydacha,
                p.SchetTcb,
                p.Name,
                p.UrLico,
                p.INN,
                p.KPP,
                p.NumDogovor,
                p.DateDogovor
            FROM 
                `partner` AS p
                LEFT JOIN partner_bank_rekviz AS r ON p.ID = r.IdPartner
            WHERE 
                p.`IsDeleted` = 0
                AND p.`IsBlocked` = 0
                AND p.`IsCommonSchetVydacha` = 0
                AND r.ID IS NOT NULL
                AND r.MinSummReestrToOplat != -1
        ')->query();

        while ($rowPart = $res->read()) {

            if ((empty($rowPart['LoginTkbVyvod']) && $rowPart['IsAutoPerevodToVydacha'] == 0) ||
                (empty($rowPart['LoginTkbPerevod']) && $rowPart['IsAutoPerevodToVydacha'] == 1)
            ) {
                continue;
            }

            $dateFrom = $dateFromNeed;
            $lastDate = $this->PrevVyvyod($rowPart['ID']);
            if ($lastDate > 0 &&
                ($lastDate + 1 < $dateFrom || $lastDate + 1 > $dateFrom)
            ) {
                $dateFrom = $lastDate + 1;
            }

            Yii::warning("VyvodSumPay: mfo=" . $rowPart['ID'] . " from " . date('d.m.Y H:i:s', $dateFrom)." to " . date('d.m.Y H:i:s', $dateTo), "rsbcron");
            echo "VyvodSumPay: mfo=" . $rowPart['ID'] . " from " . date('d.m.Y H:i:s', $dateFrom)." to " . date('d.m.Y H:i:s', $dateTo) . "\r\n";

            $sumPays = $this->GetSumPays($rowPart['ID'], $dateFrom, $dateTo);

            Yii::warning("VyvodSumPay: " . $rowPart['ID'] . " sum=" . ($sumPays/100.0), "rsbcron");
            echo "VyvodSumPay: " . $rowPart['ID'] . " sum=" . ($sumPays/100.0) . "\r\n";

            if ($sumPays > 0) {

                if ($rowPart['IsAutoPerevodToVydacha'] == 1) {
                   //перевод на счект выдачи
                    $recviz = [
                        'BIK' => TCBank::BIC,
                        'RS' => $rowPart['SchetTcb'],
                        'NamePoluchat' => $rowPart['UrLico'],
                        'INNPolushat' => $rowPart['INN'],
                        'KPPPoluchat' => $rowPart['KPP'],
                        'NaznachenPlatez' => 'Перевод средств между своими счетами согласно условий договора '.$rowPart['NumDogovor'].
                            ' от '.$rowPart['DateDogovor'].' согласно реестру за %date% г.'
                    ];
                } else {
                    //перевод на р.счект из реквизитов
                    $recviz = [
                        'BIK' => $rowPart['BIKPoluchat'],
                        'RS' => $rowPart['RaschShetPolushat'],
                        'NamePoluchat' => $rowPart['NamePoluchat'],
                        'INNPolushat' => $rowPart['INNPolushat'],
                        'KPPPoluchat' => $rowPart['KPPPoluchat'],
                        'NaznachenPlatez' => $rowPart['NaznachenPlatez']
                    ];
                }

                $TcbGate = new TcbGate($rowPart['ID'], $recviz['BIK'] == TCBank::BIC ? TCBank::$PEREVODGATE : TCBank::$VYVODGATE);
                $tkb = new TCBank($TcbGate);
                $bal = $tkb->getBalance();

                if ($bal['status'] == 1 && $bal['amount'] > $sumPays / 100.0) {

                    Yii::warning("VyvodSumPay: " . $rowPart['ID'] . " balance=" . $bal['amount'], "rsbcron");
                    echo "VyvodSumPay: " . $rowPart['ID'] . " balance=" . $bal['amount'] . "\r\n";

                    $tr = Yii::$app->db->beginTransaction();
                    Yii::$app->db->createCommand()->insert(
                        'vyvod_reestr', [
                            'DateOp' => time(),
                            'IdPartner' => $rowPart['ID'],
                            'DateFrom' => $dateFrom,
                            'DateTo' => $dateTo,
                            'SumOp' => $sumPays,
                            'StateOp' => 0,
                            'IdPay' => 0,
                            'TypePerechisl' => $recviz['BIK'] == TCBank::BIC ? 0 : 1
                        ]
                    )->execute();

                    $id = Yii::$app->db->getLastInsertID();

                    $descript = str_ireplace(
                        '%date%',(
                            date('d.m', $dateFrom) == date('d.m', $dateTo) ?
                            date('d.m', $dateFrom) :
                            date('d.m', $dateFrom) . '-' . date('d.m', $dateTo)
                        ) . '.' . date('Y', $dateTo),
                        $recviz['NaznachenPlatez']
                    );

                    $usl = $this->GetUslug($rowPart['ID'],$recviz['BIK'] == TCBank::BIC ? TU::$PEREVPAYS : TU::$VYVODPAYS);
                    if (!$usl) {
                        $tr->rollBack();
                        Yii::warning("VyvodSumPay: error mfo=" . $rowPart['ID'] . " usl=" . $usl, "rsbcron");
                        echo "VyvodSumPay: error mfo=" . $rowPart['ID'] . " usl=" . $usl . "\r\n";
                        continue;
                    }

                    $pay = new CreatePay();
                    $Provparams = new Provparams;
                    $Provparams->prov = $usl;
                    $Provparams->param = [$recviz['RS'], $recviz['BIK'], $recviz['NamePoluchat'], $recviz['INNPolushat'], $recviz['KPPPoluchat'], $descript];
                    $Provparams->summ = $sumPays;
                    $Provparams->Usluga = Uslugatovar::findOne(['ID' => $usl]);

                    $idpay = $pay->createPay($Provparams, 0, 3, TCBank::$bank, $PBKPOrg, 'reestr' . $id, 0);

                    if (!$idpay) {
                        $tr->rollBack();
                        Yii::warning("VyvodSumPay: error mfo=" . $rowPart['ID'] . " idpay=" . $idpay, "rsbcron");
                        echo "VyvodSumPay: error mfo=" . $rowPart['ID'] . " idpay=" . $idpay . "\r\n";
                        continue;
                    }
                    $idpay = $idpay['IdPay'];

                    Yii::$app->db->createCommand()->update('vyvod_reestr', [
                        'IdPay' => $idpay
                    ],'`ID` = :ID', [':ID' => $id])->execute();

                    $tr->commit();

                    Yii::warning("VyvodSumPay: mfo=" . $rowPart['ID'] . " idpay=" . $idpay, "rsbcron");
                    echo "VyvodSumPay: mfo=" . $rowPart['ID'] . " idpay=" . $idpay . "\r\n";

                    $ret = $tkb->transferToAccount([
                        'IdPay' => $idpay,
                        'account' => $recviz['RS'],
                        'bic' => $recviz['BIK'],
                        'summ' => $sumPays,
                        'name' => $recviz['NamePoluchat'],
                        'inn' => $recviz['INNPolushat'],
                        'descript' => $descript
                    ]);

                    if ($ret && $ret['status'] == 1) {
                        //сохранение номера транзакции
                        $payschets = new Payschets();
                        $payschets->SetBankTransact([
                            'idpay' => $idpay,
                            'trx_id' => $ret['transac'],
                            'url' => ''
                        ]);

                        Yii::warning("VyvodSumPay: mfo=" . $rowPart['ID'] . ", transac=" . $ret['transac'], "rsbcron");
                        echo "VyvodSumPay: mfo=" . $rowPart['ID'] . ", transac=" . $ret['transac'] . "\r\n";

                        //статус не будем смотреть
                        $payschets->confirmPay([
                            'idpay' => $idpay,
                            'result_code' => 1,
                            'trx_id' => $ret['transac'],
                            'ApprovalCode' => '',
                            'RRN' => '',
                            'message' => ''
                        ]);

                        Yii::$app->db->createCommand()->update('vyvod_reestr', [
                            'StateOp' => 1
                        ],'`ID` = :ID', [':ID' => $id])->execute();

                        $this->SendMail($bal['amount'], $sumPays/100.0,
                            $recviz['NamePoluchat']."(".$rowPart['Name'].")", $recviz['RS'],
                            $dateFrom, $idpay, $ret['transac']);
                    } else {
                        //не вывелось
                        Yii::$app->db->createCommand()->update('vyvod_reestr', [
                            'StateOp' => 2
                        ],'`ID` = :ID', [':ID' => $id])->execute();
                    }
                } else {
                    Yii::warning("VyvodSumPay: err " . $rowPart['ID'] . " balance=" . print_r($bal, true), "rsbcron");
                    echo "VyvodSumPay: err " . $rowPart['ID'] . " balance=" . print_r($bal, true) . "\r\n";

                }
            }
        }
    }

    private function PrevVyvyod($IdPartner)
    {
        $lastDateVyvod = Yii::$app->db->createCommand("
            SELECT
                `DateTo`
            FROM
                `vyvod_reestr`
            WHERE
                `IdPartner` = :IDMFO
            ORDER BY `DateTo` DESC
            LIMIT 1
        ", [':IDMFO' => $IdPartner])->queryScalar();

        return $lastDateVyvod;
    }

    /**
     * Сумма платежей для перечисления
     *
     * @param $IdPartner
     * @param $dateFrom
     * @param $dateTo
     * @return int
     * @throws \yii\db\Exception
     */
    private function GetSumPays($IdPartner, $dateFrom, $dateTo)
    {
        $existPay = Yii::$app->db->createCommand("
            SELECT
                `ID`
            FROM
                `vyvod_reestr`
            WHERE
                `IdPartner` = :IDMFO
                AND `DateFrom` >= :DATEFROM AND `DateTo` <= :DATETO                            
        ", [':IDMFO' => $IdPartner, ':DATEFROM' => $dateFrom,':DATETO' => $dateTo])->queryScalar();

        if (!$existPay) {
            $res = Yii::$app->db->createCommand("
                SELECT
                    ps.`SummPay`,
                    u.`ProvVoznagPC`,
                    u.`ProvVoznagMin`
                FROM
                    `pay_schet` AS ps
                    LEFT JOIN `uslugatovar` AS u ON ps.IdUsluga = u.ID
                WHERE
                    u.IDPartner = :IDMFO
                    AND u.IsDeleted = 0
                    AND u.IsCustom IN (".implode(",", TU::InMfo()).")
                    AND ps.`Status` = 1
                    AND ps.`DateCreate` BETWEEN :DATEFROM AND :DATETO
            ", [':IDMFO' => $IdPartner, ':DATEFROM' => $dateFrom,':DATETO' => $dateTo])->query();

            $summ = 0;
            while ($row = $res->read()) {
                $summ += $row['SummPay'];
                if ($row['ProvVoznagPC']) {
                    //вознаграждение от партнера удержать
                    $voznag = $row['SummPay'] * $row['ProvVoznagPC'] / 100.0;
                    if ($voznag < $row['ProvVoznagMin'] * 100.0) {
                        $voznag = $row['ProvVoznagMin'] * 100.0;
                    }
                    $summ -= round($voznag);
                }
            }
            return $summ;
        }
        return 0;
    }

    /**
     * Услуга для вывода
     * @param $mfo
     * @param $TypeUsl
     * @return false|string|null
     * @throws \yii\db\Exception
     */
    public function GetUslug($mfo, $TypeUsl)
    {
        return Yii::$app->db->createCommand("
            SELECT 
                `ID`
            FROM 
                `uslugatovar`
            WHERE
                `IDPartner` = 1 
                AND `ExtReestrIDUsluga` = :IDMFO 
                AND `IsCustom` = :TYPEUSL 
                AND `IsDeleted` = 0
        ", [':IDMFO' => $mfo, ':TYPEUSL' => $TypeUsl])->queryScalar();
    }

    /**
     * Оповещение о выводе
     * @param $bal
     * @param $sumPays
     * @param $NamePoluchat
     * @param $RaschShetPolushat
     * @param $date
     * @param $idpay
     * @param $transac
     */
    private function SendMail($bal, $sumPays, $NamePoluchat, $RaschShetPolushat, $date, $idpay, $transac)
    {
        $balAfter = (float)$bal - (float)$sumPays;
        $emailTo = ['ekolobov@vepay.online'];

        $mail = new SendEmail();
        $mail->send($emailTo, 'robot@vepay.online','Перечисление средств МФО',
            'Перечисление средств ' . $NamePoluchat . ' за ' . date('d.m.Y', $date) .
            ' счет ' . $RaschShetPolushat . '<br>' .
            'Сумма: ' . sprintf("%02.2f", $sumPays) . ' руб., баланс после операции: ' . sprintf("%02.2f", $balAfter) . ' руб.<br>' .
            '№ операции: ' . $idpay . ', № танзакции: ' . $transac
        );
    }

    /**
     * Праздничные дни
     *
     * @return boolean
     * @throws \yii\db\Exception
     */
    private function IsDisabledDay()
    {
        $today = date('j.m');
        $opt = Options::findOne(['Name' => 'disabledday']);
        if ($opt) {
            $days = explode(';', $opt->Value);
            foreach ($days as $day) {
                if ($day == $today) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function PlannerCommonTime()
    {
        return date('G') == 9;
    }

    /**
     * Списание со счета суммы платежей без создания платежа
     * @throws \yii\db\Exception
     */
    public function executeVirt()
    {
        $dateFrom = strtotime('yesterday');
        $dateTo = strtotime('today') - 1;

        $partners = Partner::findAll(['IsCommonSchetVydacha' => 1, 'IsDeleted' => 0]);

        foreach ($partners as $partner) {
            $sumPays = $this->GetSumPays($partner->ID, $dateFrom, $dateTo);
            if ($sumPays > 0) {
                $bal = new BalancePartner(BalancePartner::IN, $partner->ID);
                $bal->Dec($sumPays,'Перечислено на выдачу',1, 0,0);
            }
        }
    }

}