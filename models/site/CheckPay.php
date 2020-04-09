<?php

namespace app\models\site;


class CheckPay
{
    /**
     * Проверка платежа
     * @param array $data ['email2', 'order', 'date']
     * @return array ['head', 'mesg']
     */
    public function check($data)
    {
        $query = \Yii::$app->db->createCommand('
                  SELECT
                    u.`Email`, 
                    ut.NameUsluga,
                    p.`SummPay`,
                    p.`ComissSumm`,
                    p.`Status`,
                    p.DateCreate
                  FROM
                    `pay_schet` AS p
                    LEFT JOIN `user` AS u ON p.`IdUser` = u.`ID` AND u.IsDeleted = 0
                    LEFT JOIN `uslugatovar` AS ut ON ut.ID = p.IdUsluga
                  WHERE
                    p.`ID` = :ORDERID AND
                    u.`Email` = :EMAILUSER AND                   
                    p.`DateCreate` BETWEEN :DATEPAY - 86400 AND :DATEPAY + 86400                   
                  LIMIT 1
                ', [
            ":EMAILUSER" => $data['email2'],
            ":ORDERID" => $data['order'],
            ":DATEPAY" => strtotime($data['date'])
        ])
            ->queryOne();

        $head = 'Платеж не найден';
        $mesg = 'Платеж c указанными реквизитвми не найден';
        if ($query) {
            switch ($query['Status']) {
                case 0:
                    $head = "Платеж ожидает завершения оплаты";
                    break;
                case 1:
                    $head = "Платеж успешно завершен";
                    break;
                case 2:
                    $head = "Ошибка оплаты";
                    break;
            }
            $mesg =
                "Назначение платежа: ".$query['NameUsluga']."<br>".
                "Сумма: ".sprintf("%02.2f",$query['SummPay']/100.0+$query['ComissSumm']/100.0)." руб.<br>".
                "Дата: ".date("d.m.Y H:i", $query['DateCreate']);

        }

        return  ['head' => $head, 'mesg' => $mesg];
    }
}