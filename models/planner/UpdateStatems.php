<?php


namespace app\models\planner;

use app\models\mfo\statements\ReceiveStatemets;
use app\models\payonline\Partner;
use Yii;

class UpdateStatems
{
    /**
     * Фоновое обновление выписок из ТКБ
     * @throws \yii\db\Exception
     */
    public function execute()
    {
        //обновить за сегодня, раз в 6 часов - за неделю
        $dateFrom = strtotime('today');
        if (in_array(date("H"), [3, 10, 15, 21])) {
            $dateFrom = strtotime('-1 week');
        }
        $dateTo = time();

        $res = Yii::$app->db->createCommand('
            SELECT
                p.`ID`,
                p.SchetTcb,
                p.SchetTcbTransit,
                p.SchetTcbNominal
            FROM 
                `partner` AS p
            WHERE 
                p.`IsDeleted` = 0
        ')->query();

        while ($rowPart = $res->read()) {

            $partner = Partner::findOne(['ID' => $rowPart['ID']]);
            $ReceiveStatemets = new ReceiveStatemets($partner);
            //$TypeSchet 0 - счет на выдачу 1 - счет на погашение 2 - номинальный счет
            if (!empty($rowPart['SchetTcb'])) {
                $ReceiveStatemets->UpdateStatemets(0, $dateFrom, $dateTo);
            }
            if (!empty($rowPart['SchetTcbTransit'])) {
                $ReceiveStatemets->UpdateStatemets(1, $dateFrom, $dateTo);
            }
            if (!empty($rowPart['SchetTcbNominal'])) {
                $ReceiveStatemets->UpdateStatemets(2, $dateFrom, $dateTo);
            }
        }

    }

}