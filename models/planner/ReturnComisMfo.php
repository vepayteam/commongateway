<?php

//по уф мы им за счет своей доходности компенсируем комиссию эквайера. УФ платит нам по выставленному счету

namespace app\models\planner;

use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\payonline\CreatePay;
use app\models\payonline\Provparams;
use app\models\payonline\Uslugatovar;
use app\models\Payschets;
use app\models\TU;
use Yii;

class ReturnComisMfo
{
    /**
     * Компенсация комиссии некоторым МФО
     * @throws \yii\db\Exception
     */
    public function execute()
    {
        //Компенсация банковской комиссии за вывод за предыдущий месяй
        $dateFrom = strtotime('first day of last month 00:00:00');
        $dateTo = strtotime('first day of this month 00:00:00') - 1;

        Yii::warning("ReturnComisMfo: from " . date('d.m.Y H:i:s', $dateFrom)." to " . date('d.m.Y H:i:s', $dateTo), "rsbcron");
        echo "ReturnComisMfo: from " . date('d.m.Y H:i:s', $dateFrom)." to " . date('d.m.Y H:i:s', $dateTo) . "\r\n";

        $res = Yii::$app->db->createCommand("
            SELECT
                p.`ID`,
                p.`NumDogovor`,
                p.`DateDogovor`,
                p.`SchetTCBUnreserve`,
                p.`UrLico`,
                p.`INN`
            FROM 
                `partner` AS p
            WHERE 
                p.`IsDeleted` = 0
                AND p.`IsBlocked` = 0
                AND p.`IsUnreserveComis` = 1
        ")->query();

        while ($row = $res->read()) {

            if (empty($row['SchetTCBUnreserve'])) {
                continue;
            }

            $lastDate = $this->PrevVyvyod($row['ID']);
            if ($lastDate > $dateFrom) {
                continue;
            }

            Yii::warning("ReturnComisMfo: mfo=" . $row['ID'] . " from " . date('d.m.Y H:i:s', $dateFrom)." to " . date('d.m.Y H:i:s', $dateTo), "rsbcron");
            echo "ReturnComisMfo: mfo=" . $row['ID'] . " from " . date('d.m.Y H:i:s', $dateFrom)." to " . date('d.m.Y H:i:s', $dateTo) . "\r\n";

            $this->SendUnreserveComis($row, $dateFrom, $dateTo);
        }
    }

    /**
     * @param $row
     * @param $dateFrom
     * @param $dateTo
     * @throws \yii\db\Exception
     */
    private function SendUnreserveComis($row, $dateFrom, $dateTo)
    {
        $sumComis = $this->GetSumComis($row['ID'], $dateFrom, $dateTo);

        Yii::warning("ReturnComisMfo: " . $row['ID'] . " sum=" . ($sumComis/100.0), "rsbcron");
        echo "ReturnComisMfo: " . $row['ID'] . " sum=" . ($sumComis/100.0) . "\r\n";

        Yii::$app->db->createCommand()->insert(
            'vozvr_comis', [
                'DateOp' => time(),
                'IdPartner' => $row['ID'],
                'DateFrom' => $dateFrom,
                'DateTo' => $dateTo,
                'SumOp' => $sumComis,
                'StateOp' => $sumComis > 0 ? 0 : 1,
                'IdPay' => 0
            ]
        )->execute();

        $id = Yii::$app->db->getLastInsertID();

        if ($sumComis > 0) {

            $descript = "Компенсация банковской комиссии за выдачу за ".date('m.Y', $dateFrom);

            $ret = $this->ExecCompensate($row['ID'], $row['SchetTCBUnreserve'], $sumComis, $descript);

            if ($ret && $ret['status'] == 1) {

                Yii::warning("ReturnComisMfo done: mfo=" . $row['ID'] . ", sum=" . $sumComis, "rsbcron");
                echo "ReturnComisMfo done: mfo=" . $row['ID'] . ", sum=" . $sumComis . "\r\n";

                Yii::$app->db->createCommand()->update('vozvr_comis', [
                    'StateOp' => 1
                ],'`ID` = :ID', [':ID' => $id])->execute();

            } else {
                //не вывелось
                Yii::$app->db->createCommand()->update('vozvr_comis', [
                    'StateOp' => 2
                ],'`ID` = :ID', [':ID' => $id])->execute();
            }

        }

    }

    private function PrevVyvyod($IdPartner)
    {
        $lastDateVyvod = Yii::$app->db->createCommand("
            SELECT
                `DateTo`
            FROM
                `vozvr_comis`
            WHERE
                `IdPartner` = :IDMFO
            ORDER BY `DateTo` DESC
            LIMIT 1
        ", [':IDMFO' => $IdPartner])->queryScalar();

        return $lastDateVyvod;
    }

    public function GetUslug($IdPartner)
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
        ", [':IDMFO' => $IdPartner, ':TYPEUSL' => TU::$REVERSCOMIS])->queryScalar();
    }

    /**
     * Сумма комиссии для перечисления
     *
     * @param $IdPartner
     * @param $dateFrom
     * @param $dateTo
     * @return int
     * @throws \yii\db\Exception
     */
    private function GetSumComis($IdPartner, $dateFrom, $dateTo)
    {
        $existPay = Yii::$app->db->createCommand("
            SELECT
                `ID`
            FROM
                `vozvr_comis`
            WHERE
                `IdPartner` = :IDMFO
                AND `DateFrom` >= :DATEFROM AND `DateTo` <= :DATETO                            
        ", [':IDMFO' => $IdPartner, ':DATEFROM' => $dateFrom,':DATETO' =>  $dateTo])->queryScalar();

        if (!$existPay) {
            $res = Yii::$app->db->createCommand("
                SELECT
                    ps.`SummPay`,
                    u.`ProvComisPC`,
                    u.`ProvComisMin`
                FROM
                    `pay_schet` AS ps
                    LEFT JOIN `uslugatovar` AS u ON ps.IdUsluga = u.ID
                WHERE
                    u.IDPartner = :IDMFO
                    AND u.IsDeleted = 0
                    AND u.IsCustom IN (".implode(",", [TU::$TOSCHET, TU::$TOCARD]).")
                    AND ps.`Status` = 1
                    AND ps.`DateCreate` BETWEEN :DATEFROM AND :DATETO
            ", [':IDMFO' => $IdPartner, ':DATEFROM' => $dateFrom,':DATETO' =>  $dateTo])->query();

            $summ = 0;
            while ($row = $res->read()) {
                //вознаграждение от партнера удержать
                $comisBnk = $row['SummPay'] * $row['ProvComisPC'] / 100.0;
                if ($comisBnk < $row['ProvComisMin'] * 100.0) {
                    $comisBnk = $row['ProvComisMin'] * 100.0;
                }
                $summ += round($comisBnk);
            }
            return $summ;
        }
        return 0;
    }

    private function ExecCompensate($IdPartner, $schet, $summ, $description)
    {
        $dateFrom = strtotime('first day of this month 00:00:00');
        $dateTo = $dateFrom;

        $tr = Yii::$app->db->beginTransaction();
        Yii::$app->db->createCommand()->insert(
            'vyvod_system', [
                'DateOp' => time(),
                'IdPartner' => $IdPartner,
                'DateFrom' => $dateFrom,
                'DateTo' => $dateTo,
                'Summ' => $summ,
                'SatateOp' => 1,
                'IdPay' => 0,
                'TypeVyvod' => 0//pogashenie
            ]
        )->execute();
        $id = Yii::$app->db->lastInsertID;

        Yii::$app->db->createCommand()->insert('statements_account', [
            'IdPartner' => $IdPartner,
            'TypeAccount' => 0,//vydacha
            'BnkId' => 2,
            'NumberPP' => $id,
            'DatePP' => strtotime(date('d.m.Y')),
            'DateDoc' => strtotime(date('d.m.Y')),
            'DateRead' => strtotime(date('d.m.Y')),
            'SummPP' => $summ,
            'SummComis' => 0,
            'Description' => $description,
            'IsCredit' => true, //true - пополнение счета
            'Name' => "ООО ПКБП",
            'Inn' => "7728487400",
            'Kpp' => "772801001",
            'Account' => $schet,
            'Bic' => "044525388",
            'Bank' => "ТКБ БАНК ПАО",
            'BankAccount' => "30101810800000000388"
        ])->execute();

        $tr->commit();

        return ['status' => 1];
    }
}