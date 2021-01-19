<?php


namespace app\models\payonline;


use Yii;

class BalancePartner
{
    public const IN = 0;
    public const OUT = 1;

    private $IdPartner;
    private $Type;

    public function __construct($Type, $IdPartner)
    {
        $this->Type = $Type;
        $this->IdPartner = $IdPartner;
    }

    /**
     * Пополнить
     * @param $summ
     * @param $info
     * @param int $optype
     * @param int $IdPay - id pay_sceht
     * @param int $IdStatem - id statements_account
     * @throws \yii\db\Exception
     */
    public function Inc($summ, $info, $optype, $IdPay, $IdStatem)
    {
        $tr = null;
        if (!Yii::$app->db->transaction) {
            $tr = Yii::$app->db->beginTransaction();
        }

        if ($this->Type == self::OUT) {
            $this->IncOut($summ, $info, $optype, $IdPay, $IdStatem);
        } else {
            $this->IncIn($summ, $info, $optype, $IdPay, $IdStatem);
        }

        if ($tr) {
            $tr->commit();
        }
    }

    /**
     * Пополнить погашения
     * @param $summ
     * @param $info
     * @param $optype
     * @param $IdPay
     * @param $IdStatem
     * @throws \yii\db\Exception
     */
    private function IncIn($summ, $info, $optype, $IdPay, $IdStatem)
    {
        Yii::$app->db->createCommand('
            UPDATE
                `partner`
            SET
                `BalanceIn` = `BalanceIn` + :SUMM
            WHERE
                `ID` = :ID
        ', [':SUMM' => $summ, ':ID' => $this->IdPartner])->execute();

        $this->OurderIn($summ, $info, $optype, $IdPay, $IdStatem);
    }

    /**
     * Выписка погашения
     * @param $summ
     * @param $info
     * @param $optype
     * @param $IdPay
     * @param $IdStatem
     * @throws \yii\db\Exception
     */
    private function OurderIn($summ, $info, $optype, $IdPay, $IdStatem)
    {
        Yii::$app->db->createCommand('
            INSERT INTO
                `partner_orderin` 
            (
              `IdPartner`,
              `Comment`,
              `Summ`,
              `DateOp`,
              `TypeOrder`,
              `SummAfter`,
              `IdPay`,
              `IdStatm`
            ) 
            SELECT 
                p.ID,
                :INFO,
                :SUMM,
                UNIX_TIMESTAMP(),
                :TYPEOP,
                p.BalanceIn,
                :IDPAY,
                :IDSTATM
            FROM 
                 partner AS p
            WHERE
                p.ID = :ID
        ', [
            ':INFO' => mb_substr($info, 0, 250),
            ':SUMM' => $summ,
            ':TYPEOP' => $optype,
            ':ID' => $this->IdPartner,
            ':IDPAY' => $IdPay,
            ':IDSTATM' => $IdStatem
        ])->execute();
    }

    /**
     * Выписка выдача
     * @param $summ
     * @param $info
     * @param $optype
     * @param $IdPay
     * @param $IdStatem
     * @throws \yii\db\Exception
     */
    private function OurderOut($summ, $info, $optype, $IdPay, $IdStatem)
    {
        Yii::$app->db->createCommand('
            INSERT INTO
                `partner_orderout` 
            (
              `IdPartner`,
              `Comment`,
              `Summ`,
              `DateOp`,
              `TypeOrder`,
              `SummAfter`,
              `IdPay`,
              `IdStatm`             
            ) 
            SELECT 
                p.ID,
                :INFO,
                :SUMM,
                UNIX_TIMESTAMP(),
                :TYPEOP,
                p.BalanceOut,
                :IDPAY,
                :IDSTATM                   
            FROM 
                 partner AS p
            WHERE
                p.ID = :ID
        ', [
            ':INFO' => mb_substr($info, 0, 250),
            ':SUMM' => $summ,
            ':TYPEOP' => $optype,
            ':ID' => $this->IdPartner,
            ':IDPAY' => $IdPay,
            ':IDSTATM' => $IdStatem
        ])->execute();
    }

    /**
     * Пополнить выдачу
     * @param $summ
     * @param $info
     * @param $optype
     * @param $IdPay
     * @param $IdStatem
     * @throws \yii\db\Exception
     */
    private function IncOut($summ, $info, $optype, $IdPay, $IdStatem)
    {
        Yii::$app->db->createCommand('
            UPDATE
                `partner`
            SET
                `BalanceOut` = `BalanceOut` + :SUMM
            WHERE
                `ID` = :ID
        ', [':SUMM' => $summ, ':ID' => $this->IdPartner])->execute();

        $this->OurderOut($summ, $info, $optype, $IdPay, $IdStatem);
    }

    /**
     * Списать
     * @param $summ
     * @param $info
     * @param int $optype
     * @param int $IdPay - id pay_sceht
     * @param int $IdStatem - id statements_account
     * @throws \yii\db\Exception
     */
    public function Dec($summ, $info, $optype, $IdPay, $IdStatem)
    {
        $tr = null;
        if (!Yii::$app->db->transaction) {
            $tr = Yii::$app->db->beginTransaction();
        }

        if ($this->Type == self::OUT) {
            $this->DecOut($summ, $info, $optype, $IdPay, $IdStatem);
        } else {
            $this->DecIn($summ, $info, $optype, $IdPay, $IdStatem);
        }

        if ($tr) {
            $tr->commit();
        }
    }

    /**
     * Списать с погашения
     * @param $summ
     * @param $info
     * @param $optype
     * @param $IdPay
     * @param $IdStatem
     * @throws \yii\db\Exception
     */
    private function DecIn($summ, $info, $optype, $IdPay, $IdStatem)
    {
        Yii::$app->db->createCommand('
            UPDATE
                `partner`
            SET
                `BalanceIn` = `BalanceIn` - :SUMM
            WHERE
                `ID` = :ID
        ', [':SUMM' => $summ, ':ID' => $this->IdPartner])->execute();

        $this->OurderIn(-$summ, $info, $optype, $IdPay, $IdStatem);
    }

    /**
     * Списать с выдачи
     * @param $summ
     * @param $info
     * @param $optype
     * @param $IdPay
     * @param $IdStatem
     * @throws \yii\db\Exception
     */
    private function DecOut($summ, $info, $optype, $IdPay, $IdStatem)
    {
        Yii::$app->db->createCommand('
            UPDATE
                `partner`
            SET
                `BalanceOut` = `BalanceOut` - :SUMM
            WHERE
                `ID` = :ID
        ', [':SUMM' => $summ, ':ID' => $this->IdPartner])->execute();

        $this->OurderOut(-$summ, $info, $optype, $IdPay, $IdStatem);
    }

}