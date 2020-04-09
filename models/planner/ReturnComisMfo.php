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
        $dateFrom = strtotime('yesterday');
        $dateTo = strtotime('today') - 1;

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
            if ($lastDate > 0 &&
                ($lastDate + 1 < $dateFrom || $lastDate + 1 > $dateFrom)
            ) {
                $dateFrom = $lastDate + 1;
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
        $PBKPOrg = 1;
        $sumComis = $this->GetSumComis($row['ID'], $dateFrom, $dateTo);

        Yii::warning("ReturnComisMfo: " . $row['ID'] . " sum=" . ($sumComis/100.0), "rsbcron");
        echo "ReturnComisMfo: " . $row['ID'] . " sum=" . ($sumComis/100.0) . "\r\n";

        if (false && $sumComis > 0) {

            $tr = Yii::$app->db->beginTransaction();
            Yii::$app->db->createCommand()->insert(
                'vozvr_comis', [
                    'DateOp' => time(),
                    'IdPartner' => $row['ID'],
                    'DateFrom' => $dateFrom,
                    'DateTo' => $dateTo,
                    'SumOp' => $sumComis,
                    'StateOp' => 0,
                    'IdPay' => 0
                ]
            )->execute();

            $id = Yii::$app->db->getLastInsertID();

            $descript = "Перевод между собственными счетами согласно условий договора №".$row['NumDogovor']." от ".$row['DateDogovor']." за ".date('d.m', $dateFrom);

            $usl = $this->GetUslug($row['ID']);
            if (!$usl) {
                $tr->rollBack();
                Yii::warning("ReturnComisMfo: error mfo=" . $row['ID'] . " usl=" . $usl, "rsbcron");
                echo "ReturnComisMfo: error mfo=" . $row['ID'] . " usl=" . $usl . "\r\n";
                return;
            }

            $pay = new CreatePay();
            $Provparams = new Provparams;
            $Provparams->prov = $usl;
            $Provparams->param = [$row['SchetTCBUnreserve'], TCBank::BIC, $row['UrLico'], $row['INN'], '', $descript];
            $Provparams->summ = $sumComis;
            $Provparams->Usluga = Uslugatovar::findOne(['ID' => $usl]);

            $idpay = $pay->createPay($Provparams, 0, 3, TCBank::$bank, $PBKPOrg, 'revcomis' . $id);

            if (!$idpay) {
                $tr->rollBack();
                Yii::warning("ReturnComisMfo: error mfo=" . $row['ID'] . " idpay=" . $idpay, "rsbcron");
                echo "ReturnComisMfo: error mfo=" . $row['ID'] . " idpay=" . $idpay . "\r\n";
                return;
            }
            $idpay = $idpay['IdPay'];

            Yii::$app->db->createCommand()->update('vyvod_reestr', [
                'IdPay' => $idpay
            ],'`ID` = :ID', [':ID' => $id])->execute();

            $tr->commit();

            Yii::warning("ReturnComisMfo: mfo=" . $row['ID'] . " idpay=" . $idpay, "rsbcron");
            echo "ReturnComisMfo: mfo=" . $row['ID'] . " idpay=" . $idpay . "\r\n";

            $TcbGate = new TcbGate($row['ID'], TCBank::$PEREVODGATE);
            $tkb = new TCBank($TcbGate);

            $ret = $tkb->transferToAccount([
                'IdPay' => $idpay,
                'account' => $row['SchetTCBUnreserve'],
                'bic' => TCBank::BIC,
                'summ' => $sumComis,
                'name' => $row['UrLico'],
                'inn' => $row['INN'],
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

                Yii::warning("ReturnComisMfo: mfo=" . $row['ID'] . ", transac=" . $ret['transac'], "rsbcron");
                echo "ReturnComisMfo: mfo=" . $row['ID'] . ", transac=" . $ret['transac'] . "\r\n";

                //статус не будем смотреть
                $payschets->confirmPay([
                    'idpay' => $idpay,
                    'result_code' => 1,
                    'trx_id' => $ret['transac'],
                    'ApprovalCode' => '',
                    'RRN' => '',
                    'message' => ''
                ]);

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
}