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
     * @param $sum
     * @param $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    public function Inc($sum, $info, int $opType, int $idPay, int $idStatm)
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
     * @param $sum
     * @param $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    private function IncIn($sum, $info, int $opType, int $idPay, int $idStatm)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        $partner->BalanceIn += $sum;
        $partner->save();

        $this->OrderIn($partner, $sum, $info, $opType, $idPay, $idStatm);
    }

    /**
     * Пополнить выдачу
     * @param $sum
     * @param $info
     * @param int $opType
     * @param int $IdPay
     * @param int $IdStatm
     * @throws \yii\db\Exception
     */
    private function IncOut($sum, $info, int $opType, int $IdPay, int $IdStatm)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        $partner->BalanceOut += $sum;
        $partner->save();

        $this->OrderOut($partner, $sum, $info, $opType, $IdPay, $IdStatm);
    }

    /**
     * Списать
     * @param $sum
     * @param $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    public function Dec($sum, $info, int $opType, int $idPay, int $idStatm)
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
     * @param $sum
     * @param $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    private function DecIn($sum, $info, int $opType, int $idPay, int $idStatm)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        $partner->BalanceIn -= $sum;
        $partner->save();

        $this->OrderIn($partner, -$sum, $info, $opType, $idPay, $idStatm);
    }

    /**
     * Списать с выдачи
     * @param $sum
     * @param $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    private function DecOut($sum, $info, int $opType, int $idPay, int $idStatm)
    {
        $partner = Partner::findOne(['ID' => $this->IdPartner]);
        $partner->BalanceOut -= $sum;
        $partner->save();

        $this->OrderOut($partner, -$sum, $info, $opType, $idPay, $idStatm);
    }

    /**
     * Выписка погашения
     * @param Partner $partner
     * @param $sum
     * @param $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     * @throws \yii\db\Exception
     */
    private function OrderIn(Partner $partner, $sum, $info, int $opType, int $idPay, int $idStatm)
    {
        $orderIn = new PartnerOrderIn();
        $orderIn->IdPartner = $partner->ID;
        $orderIn->Comment = mb_substr($info, 0, 250);
        $orderIn->Summ = $sum;
        $orderIn->DateOp = time();
        $orderIn->TypeOrder = $opType;
        $orderIn->SummAfter = $partner->BalanceIn;
        $orderIn->IdPay = $idPay;
        $orderIn->IdStatm = $idStatm;
        $orderIn->save();
    }

    /**
     * Выписка выдача
     * @param Partner $partner
     * @param $sum
     * @param $info
     * @param int $opType
     * @param int $idPay
     * @param int $idStatm
     */
    private function OrderOut(Partner $partner, $sum, $info, int $opType, int $idPay, int $idStatm)
    {
        $orderIn = new PartnerOrderOut();
        $orderIn->IdPartner = $partner->ID;
        $orderIn->Comment = mb_substr($info, 0, 250);
        $orderIn->Summ = $sum;
        $orderIn->DateOp = time();
        $orderIn->TypeOrder = $opType;
        $orderIn->SummAfter = $partner->BalanceOut;
        $orderIn->IdPay = $idPay;
        $orderIn->IdStatm = $idStatm;
        $orderIn->save();
    }
}
