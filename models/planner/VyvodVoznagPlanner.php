<?php

namespace app\models\planner;

use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\Options;
use app\models\partner\admin\VoznagStat;
use app\models\partner\admin\VyvodVoznag;
use Yii;

class VyvodVoznagPlanner
{
    /**
     * Вывод вознаграждения за предыдущую неделю в планировщике
     *
     * @throws \yii\db\Exception
     */
    public function execute()
    {
        if ($this->IsDisabledDay()) {
            //в праздничные дни не выгружать
            Yii::warning("VyvodVoznagPlanner: disabled day", "rsbcron");
            echo "VyvodVoznagPlanner: disabled day" . "\r\n";
            return;
        }

        $week = date('W');
        if ($week > 1) {
            $dateFrom = strtotime(date('Y') . 'W' . sprintf("%02d", $week - 1));
            $dateTo = strtotime(date('Y') . 'W' . sprintf("%02d", $week)) - 1;
        } else {
            $dateFrom = strtotime((date('Y') - 1) . 'W52');
            $dateTo = strtotime(date('Y') . 'W' . sprintf("%02d", $week)) - 1;
        }

        Yii::warning("VyvodVoznagPlanner: from " . date('d.m.Y H:i:s', $dateFrom)." to " . date('d.m.Y H:i:s', $dateTo), "rsbcron");
        echo "VyvodVoznagPlanner: from " . date('d.m.Y H:i:s', $dateFrom)." to " . date('d.m.Y H:i:s', $dateTo) . "\r\n";

        $res = Yii::$app->db->createCommand('
            SELECT
                p.`ID`,
                p.`LoginTkbVyvod`,
                p.`KeyTkbVyvod`,
                p.NumDogovor,
                r.INNPolushat
            FROM 
                `partner` AS p
                LEFT JOIN partner_bank_rekviz AS r ON p.ID = r.IdPartner
            WHERE 
                p.`IsDeleted` = 0
                AND p.`IsBlocked` = 0
                AND r.ID IS NOT NULL
        ')->query();

        while ($rowPart = $res->read()) {

            if (empty($rowPart['LoginTkbVyvod'])) {
                continue;
            }

            $sumVozn = $this->GetSumVoznag($rowPart['ID'], $dateFrom, $dateTo);

            Yii::warning("VyvodVoznagPlanner: " . $rowPart['ID'] . " sum=" . $sumVozn, "rsbcron");
            echo "VyvodVoznagPlanner: " . $rowPart['ID'] . " sum=" . $sumVozn . "\r\n";

            if ($sumVozn > 0) {

                $TcbGate = new TcbGate($rowPart['ID'], TCBank::$VYVODGATE);
                $tkb = new TCBank($TcbGate);
                $bal = $tkb->getBalance();

                if ($bal['status'] == 1 && $bal['amount'] > $sumVozn / 100.0) {
                    $VyvodVoznag = new VyvodVoznag();
                    $VyvodVoznag->setAttributes([
                        'partner' => $rowPart['ID'],
                        'summ' => $sumVozn,
                        'datefrom' => date("d.m.Y H:i", $dateFrom),
                        'dateto' => date("d.m.Y H:i", $dateTo),
                        'isCron' => true,
                        'type' => 0,
                        'balance' => $bal['amount']
                    ]);
                    $VyvodVoznag->CreatePayVyvod();
                }
            }
        }

    }

    /**
     * Сумма вознаграждения для вывода
     *
     * @param $IdPartner
     * @param $dateFrom
     * @param $dateTo
     * @return int|mixed
     * @throws \yii\db\Exception
     */
    private function GetSumVoznag($IdPartner, $dateFrom, $dateTo)
    {
        $existPay = Yii::$app->db->createCommand("
            SELECT
                `ID`
            FROM
                `vyvod_system`
            WHERE
                `IdPartner` = :IDMFO
                AND `TypeVyvod` = 0
                AND `DateTo` > :DATEFROM                          
        ", [':IDMFO' => $IdPartner, ':DATEFROM' =>  $dateFrom])->queryScalar();

        if (!$existPay) {
            $voznStat = new VoznagStat();
            $voznStat->setAttributes([
                'datefrom' => date("d.m.Y H:i", $dateFrom),
                'dateto' => date("d.m.Y H:i", $dateTo),
                'IdPart' => $IdPartner,
                'TypeUslug' => 1
            ]);
            $sumVozn = $voznStat->GetSummVoznag();
            return $sumVozn;
        }

        return 0;
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

}