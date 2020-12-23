<?php


namespace app\services\cards;

use Yii;

/**
 * Class DeleteOldPan
 * @package app\services\cards
 */
class DeleteOldPan
{
    /**
     * Замечено нарушение целостности данных между таблицами cards и pan_token, по этому написал два запроса.
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function exec(): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            self::deleteAsCard();
            self::deleteAsPanToken();
            $transaction->commit();
            echo 'success';
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws \yii\db\Exception
     */
    private function deleteAsCard(): void
    {
        //Получаем возможные значения поля SrokKard за последние пять лет.
        $currentYear = date ( 'y' );
        $currentMonth = date ( 'm' );
        $srokKard = [];
        for ($i = 1; $i <= $currentMonth - 1; $i++) {
            $srokKard[] = $i . $currentYear;
        }
        for($y = 1; $y < 5; $y++) {
            for($m = 1; $m <= 12; $m++) {
                $srokKard[] = $m . ($currentYear - $y) . " ";
            }
        }
        $srokKard =  implode(', ', $srokKard);
        $sql = "UPDATE cards c
        LEFT JOIN pan_token pt ON pt.ID = c.IdPan
		SET pt.EncryptedPAN = null
        WHERE (c.DateAdd < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 5 YEAR)) AND pt.EncryptedPAN IS NOT NULL)
        OR (c.SrokKard IN ($srokKard) AND pt.EncryptedPAN IS NOT NULL)";
        Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * @throws \yii\db\Exception
     */
    public function deleteAsPanToken(): void
    {
        $currentYear = date ( 'y' );
        $currentMonth = date ( 'm' );
        $sql = "UPDATE pan_token pt
        SET EncryptedPAN = null
        WHERE (pt.CreatedDate < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 5 YEAR))  AND pt.EncryptedPAN IS NOT NULL)
        OR (pt.ExpDateYear <= :Y AND pt.ExpDateMonth < :M AND pt.ExpDateYear > 0  AND pt.EncryptedPAN IS NOT NULL)
        ORDER BY CreatedDate DESC";
        Yii::$app->db->createCommand($sql,[':Y' => $currentYear, ':M' => $currentMonth])->execute();
    }
}