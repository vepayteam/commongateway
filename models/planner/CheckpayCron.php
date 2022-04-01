<?php

namespace app\models\planner;

use app\models\Payschets;
use app\services\payment\jobs\RefreshStatusPayJob;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

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
        $this->cancelOldPayments();

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
            $isCheckedTimeExecQuery = false;
            $startQuery = microtime(true);


            $query = PaySchet::find()
                ->needCheckStatusByRsbcron()
                ->select([
                    PaySchet::tableName() . '.ID',
                    PaySchet::tableName() . '.Status',
                    PaySchet::tableName() . '.ExtBillNumber',
                    PaySchet::tableName() . '.IdUsluga',
                ]);

            foreach ($query->batch() as $paySchets) {
                if(!$isCheckedTimeExecQuery) {
                    Yii::warning('CheckpayCron checkStatePay queryTime: ' . (microtime(true) - $startQuery));
                    $isCheckedTimeExecQuery = true;
                }

                /** @var PaySchet $paySchet */
                foreach ($paySchets as $paySchet) {
                    $paySchet->Status = PaySchet::STATUS_WAITING_CHECK_STATUS;
                    $paySchet->ErrorInfo = 'Ожидает запрос статуса';
                    $paySchet->save(false);
                    Yii::warning('CheckpayCron checkStatePay pushed: ID=' . $paySchet->ID);
                    Yii::$app->queue->push(new RefreshStatusPayJob([
                        'paySchetId' =>  $paySchet->ID,
                    ]));
                }
            }
        } catch (\Exception $e) {
            // в случае возникновения ошибки при выполнении одного из запросов выбрасывается исключение
            echo "error: " . $e->getMessage() . "\n";
            Yii::warning("checkStatePay-error: " . $e->getMessage(), 'rsbcron');
            Yii::warning($e->getTraceAsString(), 'rsbcron');
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
    private function cancelOldPayments()
    {
        try {
            $paySchets = PaySchet::find()
                ->andWhere(['=', 'Status', PaySchet::STATUS_WAITING])
                ->andWhere(['is', 'ExtBillNumber', null])
                ->andWhere(['<', 'DateLastUpdate', new Expression('UNIX_TIMESTAMP() - (TimeElapsed * 2)')])
                ->joinWith(['uslugatovar' => function (ActiveQuery $query) {
                    return $query->andWhere(['!=', 'uslugatovar.IsCustom', UslugatovarType::TOCARD]);
                }])
                ->all();

            /** @var PaySchet $paySchet */
            foreach ($paySchets as $paySchet) {
                if (self::delayPaymentCancellation($paySchet)) {
                    continue;
                }

                $ps = new Payschets();
                $ps->confirmPay([
                    'idpay' => $paySchet->ID,
                    'idgroup' => $paySchet->IdGroupOplat,
                    'trx_id' => $paySchet->ExtBillNumber,
                    'result_code' => 2,
                    'message' => PaySchet::ERROR_INFO_PAYMENT_TIMEOUT,
                    'RCCode' => PaySchet::RCCODE_CANCEL_PAYMENT, // VPBC-1293 для операций с таймаутом устанавливать RCCode=TL
                    'ApprovalCode' => '',
                    'RRN' => '',
                ]);
                Yii::info('CheckpayCron cancelOldPayments cancel payment paySchet.ID=' . $paySchet->ID, 'rsbcron');
            }
        } catch (\Exception $e) {
            Yii::error(['CheckpayCron cancelOldPayments exception', $e], 'rsbcron');
        }
    }

    /**
     * Проверяет нужно ли отложить отмену платежа
     *
     * @param PaySchet $paySchet
     * @return bool Если нужно пропустить отмену платежа - true, иначе false
     */
    private static function delayPaymentCancellation(PaySchet $paySchet): bool
    {
        if ($paySchet->sms_accept !== 0) {
            return false;
        }

        // По операциям на вывод средств инвесторами, выполненным после 18.00, прошу установить время подтверждения операции по СМС – 20 часов (вместо 4-х).
        // Если при этом следующий день является не рабочим суббота или воскресенье, то к 20 часам необходимо прибавить 24 ч или 48 ч соответственно.

        /**
         * Порядковый номер недели от 0 (воскресенье) до 6 (суббота)
         */
        $week = (int)date('w', $paySchet->DateCreate);

        /**
         * Часы в 24-часовом формате без ведущего нуля
         */
        $hour = (int)date('G', $paySchet->DateCreate);

        $smsTimer = 4 * 3600;
        if (($week >= 1 && $week < 5) && ($hour >= 18 || $hour < 9)) {
            // пн-чт после 18
            $smsTimer = 20 * 3600;
        } elseif ($week == 5 && $hour >= 18) {
            // пт после 18
            $smsTimer = (20 + 72) * 3600;
        } elseif ($week == 6) {
            // сб
            $smsTimer = (20 + 48) * 3600;
        } elseif ($week == 0) {
            // вс
            $smsTimer = (20 + 24) * 3600;
        }

        if ($paySchet->DateCreate > time() - $smsTimer) {
            return true;
        }

        return false;
    }
}
