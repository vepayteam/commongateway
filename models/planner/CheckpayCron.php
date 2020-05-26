<?php

namespace app\models\planner;

use app\models\bank\BankMerchant;
use app\models\bank\TCBank;
use app\models\Payschets;
//use app\models\protocol\OnlineProv;
use app\models\TU;
use Yii;


/**
 * Class CheckpayCron
 * @property int $timeOutMin Таймаут отмены платежа в минутах
 * @package app\models
 */
class CheckpayCron
{
    //private $timeOutMin = 15;

    public function execute()
    {
        //проверка статуса оплаты, если не вернулся в магазин клиент
        $this->checkStatePay();

        //отмена старых
        $this->cancelOldsPay();

        //экспорт оплаченных, но не выгруженных
        //$this->checkToExport();
    }

    /**
     * Проверка статуса оплаты, если не вернулся в магазин клиент
     */
    private function checkStatePay()
    {
        //запрос статуса надо делать по платежам у которых оборвалась обработка и они остались с `Status`=0
        //чтобы их с текущими не путать, можно по DateLastUpdate > 10 минут смотреть.
        //остановить запрос статуса через 1 час (или cancelorder можно посылать)

        $connection = Yii::$app->db;

        try {

            $query = $connection->createCommand('
                SELECT
                    m.ID,
                    m.ExtBillNumber,
                    m.Status,
                    m.DateLastUpdate,
                    m.Bank,
                    us.IsCustom
                FROM
                    pay_schet AS m
                    LEFT JOIN uslugatovar AS us ON us.ID = m.IdUsluga
                WHERE
                    m.Status = 0 AND 
                    m.sms_accept = 1
                    AND (
                        (us.IsCustom NOT IN (11,13,12,16) AND m.DateLastUpdate < UNIX_TIMESTAMP() - m.TimeElapsed) 
                        OR (us.IsCustom IN (11,13,12,16) AND m.DateLastUpdate < :NOTIMEOUT)
                    )
                    AND m.DateLastUpdate > :TIMEOUTOFF                     
            ', [
                //":TIMEOUT" => time() - $this->timeOutMin * 60,
                ":NOTIMEOUT" => time() - 60,
                ":TIMEOUTOFF" => time() - 3600 * 24 * 30,
            ])
                ->query();

            if ($query) {
                while ($value = $query->read()) {
                    if (!empty($value['ExtBillNumber'])) {

                        $bank = $value['Bank'];
                        $order = '0';
                        if ($value['Bank'] == TCBank::$bank) {
                            //ткб
                            $order = $value['ID'];
                        }

                        $merchBank = BankMerchant::Get($bank);
                        $mesg = $merchBank->confirmPay($order, 0, true);

                        echo "check " . $value['ID'] . " - " . $value['ExtBillNumber'] . " - " . $mesg['message'] . "\n";
                        Yii::warning("check " . $value['ID'] . " - " . $value['ExtBillNumber'] . " - " . $mesg['message'] . "\r\n", 'rsbcron');
                    }
                }
            }
        } catch (\Exception $e) {
            // в случае возникновения ошибки при выполнении одного из запросов выбрасывается исключение
            echo "error: " . $e->getMessage() . "\n";
            Yii::warning("checkStatePay-error: " . $e->getMessage(), 'rsbcron');
        }

    }

    /**
     * Экспорт оплаченных, но не выгруженных
     */
    private function checkToExport()
    {
        /*$connection = Yii::$app->db;

        try {
            $query = $connection->createCommand('
                    SELECT
                        m.ID
                    FROM
                        pay_schet AS m
                        INNER JOIN uslugatovar AS us 
                          ON (us.ID = m.IdUsluga AND us.`TypeExport` = 0 AND us.ProfitIdProvider > 0)
                    WHERE
                        m.Status = 1
                        AND m.`DateOplat` > 0
                        AND m.`UserClickPay` = 1
                        AND m.`CountSendOK` = 10
                        AND m.DateLastUpdate < :TIMEOUT
                        AND m.`ID` NOT IN (SELECT IdSchet FROM export_pay)
            ', [
                ":TIMEOUT" => time() - 5 * 60
            ])
                ->query();

            $rows = $query->readAll();

            if (!empty($rows)) {
                foreach ($rows as $keys => $value) {
                    $Order_ID = $value['ID'];
                    // экспорт платежа
                    Yii::warning('Export: ID_SCHET: ' . $Order_ID, 'rsbcron');

                    $exportpay = new OnlineProv();
                    $exportpay->makePay($Order_ID);
                }
            }

        } catch (\Exception $e) {
            // в случае возникновения ошибки при выполнении одного из запросов выбрасывается исключение
            Yii::warning("checkToExport-error: " . $e->getMessage(), 'rsbcron');
        }*/
    }

    /**
     * Отменить старые платежи (не отправленные в банк)
     */
    private function cancelOldsPay()
    {
        try {

            $res = Yii::$app->db->createCommand('
                SELECT
                    m.ID,
                    m.IdGroupOplat,
                    m.Status,
                    m.DateLastUpdate,
                    m.ExtBillNumber,
                    m.sms_accept,
                    m.DateCreate,
                    us.IsCustom
                FROM
                    pay_schet AS m
                    LEFT JOIN uslugatovar AS us ON us.ID = m.IdUsluga
                WHERE
                    m.Status = 0  
                    AND m.ExtBillNumber IS NULL
                    AND m.UserClickPay = 0
                    AND m.DateLastUpdate < UNIX_TIMESTAMP() - m.TimeElapsed * 2                    
            ')->query();

            while ($row = $res->read()) {
                if ($row['sms_accept'] == 0) {
                    //По операциям на вывод средств инвесторами, выполненным после 18.00, прошу установить время подтверждения операции по СМС  – 20 часов (вместо 4-х).
                    //Если при этом следующий день является не рабочим суббота или воскресенье, то к 20 часам необходимо прибавить 24 ч или 48 ч соответственно .
                    $wd = date('w', $row['DateCreate']);
                    $h = date('G', $row['DateCreate']);
                    $smsTimer = 4 * 3600;
                    if (($h >= 18 || $h < 9) && $wd >= 1 && $wd < 5) {
                        //пн-чт после 18
                        $smsTimer = 20 * 3600;
                    } elseif ($wd == 5 && $h >= 18) {
                        //пт после 18
                        $smsTimer = (20 + 72) * 3600;
                    } elseif ($wd == 6) {
                        //сб
                        $smsTimer = (20 + 48) * 3600;
                    } elseif ($wd == 0) {
                        //вс
                        $smsTimer = (20 + 24) * 3600;
                    }

                    if ($row['DateCreate'] > time() - $smsTimer) {
                        continue;
                    }
                }

                $ps = new Payschets();
                $ps->confirmPay([
                    'idpay' => $row['ID'],
                    'idgroup' => $row['IdGroupOplat'],
                    'result_code' => 2,
                    'trx_id' => $row['ExtBillNumber'],
                    'ApprovalCode' => '',
                    'RRN' => '',
                    'message' => 'Время оплаты истекло'
                ]);
                Yii::warning("cancelOldsPay: " . $row['ID'], 'rsbcron');
            }
        } catch (\yii\db\Exception $e) {
            // в случае возникновения ошибки при выполнении одного из запросов выбрасывается исключение
            Yii::warning("checkToExport-error: " . $e->getMessage(), 'rsbcron');
        } catch (\Exception $e) {
            // в случае возникновения ошибки при выполнении одного из запросов выбрасывается исключение
            Yii::warning("checkToExport-error: " . $e->getMessage(), 'rsbcron');
        }
    }
}