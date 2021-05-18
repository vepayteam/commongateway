<?php


namespace app\models\kfapi;

use app\models\mfo\MfoBalance;
use app\models\payonline\Partner;
use app\models\TU;
use Yii;
use yii\base\Model;

class KfBalance extends Model
{
    public $account;

    public function rules()
    {
        return [
            [['account'], 'string', 'length' => [20]]
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    public function GetInSums($IdPartner)
    {
        $ret = Yii::$app->db->createCommand("
            SELECT
                SUM(ps.SummPay)
            FROM
                `pay_schet` AS ps
                LEFT JOIN uslugatovar AS u ON ps.IdUsluga = u.ID
            WHERE
                u.IDPartner = :PARTNER
                AND u.IsCustom IN (" . implode(",", [TU::$POGASHECOM, TU::$POGASHATF, TU::$AVTOPLATATF, TU::$AVTOPLATECOM]) . ")
                AND ps.Status = 1
                AND ps.DateCreate BETWEEN :DATEFROM AND :DATETO
        ", [
            ":PARTNER" => $IdPartner,
            ":DATEFROM" => mktime(0, 0, 0, date("n"), date("j"), date("Y")),
            ":DATETO" => time()
        ])->queryScalar();
        return intval($ret);
    }

    public function GetOutSums($IdPartner)
    {
        $ret = Yii::$app->db->createCommand("
            SELECT
                SUM(ps.SummPay)
            FROM
                `pay_schet` AS ps
                LEFT JOIN uslugatovar AS u ON ps.IdUsluga = u.ID
            WHERE
                u.IDPartner = :PARTNER
                AND u.IsCustom IN (" . implode(",", [TU::$TOSCHET]) . ")
                AND ps.Status = 1
                AND ps.DateCreate BETWEEN :DATEFROM AND :DATETO
        ", [
            ":PARTNER" => $IdPartner,
            ":DATEFROM" => mktime(0, 0, 0, date("n"), date("j"), date("Y")),
            ":DATETO" => time()
        ])->queryScalar();
        return intval($ret);
    }

    /**
     * @deprecated
     * Баланс краутфандинга и МФО с учётом комиссий
     * @param Partner $partner
     * @return double|null
     * @throws \yii\db\Exception
     */
    public function GetBalance(Partner $partner)
    {
        $bal = new MfoBalance($partner);
        $out = $bal->GetBalanceWithoutLocal();

        $b = null;
        if ($partner->SchetTcb == $this->account) {
            //транзитный КФ и транзитный выдача
            $b = $out['localout'];
        } elseif ($partner->SchetTcbNominal == $this->account || $partner->SchetTcbTransit == $this->account) {
            //номинальный КФ и транзитный погашение
            $b = $out['localin'];
        }

        return $b;
    }
}