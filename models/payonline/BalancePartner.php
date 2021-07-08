<?php

namespace app\models\payonline;

use Yii;

class BalancePartner
{
    private const MAX_COMMENT_LENGTH = 250;

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
     * @param int $sum
     * @param string $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    public function Inc(int $sum, string $info, int $opType, int $idPay, int $idStatm)
    {
        $tr = null;
        if (!Yii::$app->db->transaction) {
            $tr = Yii::$app->db->beginTransaction();
        }

        if ($this->Type == self::OUT) {
            $this->IncOut($sum, $info, $opType, $idPay, $idStatm);
        } else {
            $this->IncIn($sum, $info, $opType, $idPay, $idStatm);
        }

        if ($tr) {
            $tr->commit();
        }
    }

    /**
     * Пополнить погашения
     * @param int $sum
     * @param string $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    private function IncIn(int $sum, string $info, int $opType, int $idPay, int $idStatm)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        $partner->BalanceIn += $sum;
        $partner->save(false);

        $this->OrderIn($partner, $sum, $info, $opType, $idPay, $idStatm);
    }

    /**
     * Пополнить выдачу
     * @param int $sum
     * @param string $info
     * @param int $opType
     * @param int $IdPay
     * @param int $IdStatm
     * @throws \yii\db\Exception
     */
    private function IncOut(int $sum, string $info, int $opType, int $IdPay, int $IdStatm)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        $partner->BalanceOut += $sum;
        $partner->save(false);

        $this->OrderOut($partner, $sum, $info, $opType, $IdPay, $IdStatm);
    }

    /**
     * Списать
     * @param int $sum
     * @param string $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    public function Dec(int $sum, string $info, int $opType, int $idPay, int $idStatm)
    {
        $tr = null;
        if (!Yii::$app->db->transaction) {
            $tr = Yii::$app->db->beginTransaction();
        }

        if ($this->Type == self::OUT) {
            $this->DecOut($sum, $info, $opType, $idPay, $idStatm);
        } else {
            $this->DecIn($sum, $info, $opType, $idPay, $idStatm);
        }

        if ($tr) {
            $tr->commit();
        }
    }

    /**
     * Списать с погашения
     * @param int $sum
     * @param string $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    private function DecIn(int $sum, string $info, int $opType, int $idPay, int $idStatm)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        $partner->BalanceIn -= $sum;
        $partner->save(false);

        $this->OrderIn($partner, -$sum, $info, $opType, $idPay, $idStatm);
    }

    /**
     * Списать с выдачи
     * @param int $sum
     * @param string $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    private function DecOut(int $sum, string $info, int $opType, int $idPay, int $idStatm)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        $partner->BalanceOut -= $sum;
        $partner->save(false);

        $this->OrderOut($partner, -$sum, $info, $opType, $idPay, $idStatm);
    }

    /**
     * Выписка погашения
     * @param Partner $partner
     * @param int $sum
     * @param string $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    private function OrderIn(Partner $partner, int $sum, string $info, int $opType, int $idPay, int $idStatm)
    {
        $orderIn = new PartnerOrderIn();
        $orderIn->IdPartner = $partner->ID;
        $orderIn->Comment = mb_substr($info, 0, self::MAX_COMMENT_LENGTH);
        $orderIn->Summ = $sum;
        $orderIn->DateOp = time();
        $orderIn->TypeOrder = $opType;
        $orderIn->SummAfter = $partner->BalanceIn;
        $orderIn->IdPay = $idPay;
        $orderIn->IdStatm = $idStatm;
        $orderIn->save(false);
    }

    /**
     * Выписка выдача
     * @param Partner $partner
     * @param int $sum
     * @param string $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     */
    private function OrderOut(Partner $partner, int $sum, string $info, int $opType, int $idPay, int $idStatm)
    {
        $orderOut = new PartnerOrderOut();
        $orderOut->IdPartner = $partner->ID;
        $orderOut->Comment = mb_substr($info, 0, self::MAX_COMMENT_LENGTH);
        $orderOut->Summ = $sum;
        $orderOut->DateOp = time();
        $orderOut->TypeOrder = $opType;
        $orderOut->SummAfter = $partner->BalanceOut;
        $orderOut->IdPay = $idPay;
        $orderOut->IdStatm = $idStatm;
        $orderOut->save(false);
    }
}
