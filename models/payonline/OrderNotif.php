<?php


namespace app\models\payonline;


use app\models\SendEmail;
use Yii;

class OrderNotif
{
    public function SendNotif(OrderPay $order)
    {
        if (!empty($order->EmailTo)) {
            Yii::$app->db->createCommand()->insert('order_notif', [
                'IdOrder' => $order->ID,
                'DateAdd' => time(),
                'DateSended' => 0,
                'TypeSend' => 0,
                'StateSend' => 0
            ])->execute();

            Yii::$app->db->createCommand()->update('order_pay', [
                'EmailSended' => time(),
            ], ['ID' => $order->ID])->execute();
        }

        if (!empty($order->SmsTo)) {
            Yii::$app->db->createCommand()->insert('order_notif', [
                'IdOrder' => $order->ID,
                'DateAdd' => time(),
                'DateSended' => 0,
                'TypeSend' => 1,
                'StateSend' => 0
            ])->execute();

            Yii::$app->db->createCommand()->update('order_pay', [
                'SmsSended' => time(),
            ], ['ID' => $order->ID])->execute();
        }
    }

    public function SendEmails()
    {
        $res = Yii::$app->db->createCommand("
            SELECT
                onf.`ID`,
                o.EmailTo,
                onf.IdOrder,
                o.Comment,
                o.SumOrder,
                o.OrderTo
            FROM
                `order_notif` AS onf
                LEFT JOIN `order_pay` AS o ON o.ID = onf.IdOrder
            WHERE
                o.StateOrder = 0
                AND onf.DateSended = 0
                AND onf.TypeSend = 0
        ")->query();

        while ($row = $res->read()) {

            $subject = "Счет на оплату";
            $content = "Счет № ".$row['IdOrder']." на сумму ".($row['SumOrder']/100.0)." руб.<br>".
                $row['Comment']."<br>";
            if($row['OrderTo'] && $orderTo = json_decode($row['OrderTo'])) {
                $table = "<table><thead><tr><td>№</td><td>Наименование товара</td><td>Кол-во</td><td>сумма</td></tr></thead>";
                $tbody = "<tbody>";
                foreach($orderTo as $key => $ord) {
                    $tbody .= "<tr>";
                    $tbody .= "<td>" . ($key + 1) . "</td>";
                    $tbody .= "<td>" . $ord->name . "</td>";
                    $tbody .= "<td>" . $ord->qnt . "</td>";
                    $tbody .= "<td>" . $ord->sum . "</td>";
                    $tbody .= "</tr>";
                }
                $tbody .= "</tbody>";

                $content .= $table . $tbody . "</table>";
            }
            $content .= "<a href='https://api.vepay.online/widget/order/".$row['IdOrder']."'>Оплатить</a>";

            $mail = new SendEmail();
            $mail->send($row['EmailTo'], null, $subject, $content);

            Yii::$app->db->createCommand()->update('order_notif', [
                'DateSended' => time(),
                'StateSend' => 1,
            ], ['ID' => $row['ID']])->execute();
        }
    }
}