<?php

namespace app\models\antifraud\partner;

use app\models\antifraud\tables\AFFingerPrit;
use app\models\partner\stat\PayShetStat;
use app\models\partner\UserLk;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\VarDumper;

/**
 * @property array $data
 * @property PayShetStat $pay_schet
 */
class AntiFraudModel extends PayShetStat
{
    private $data;
    private $pay_schet;
    private $record_model;

    public function getList($IsAdmin, $page = 0, $nolimit = 0)
    {
        $query = new Query();
        $query->select([
            'ps.ID',
            'ps.Extid',
            'qp.NameUsluga',
            'ps.SummPay',
            'ps.ComissSumm',
            'ps.DateCreate',
            'ps.DateOplat',
            'ps.PayType',
            'ps.ExtBillNumber',
            'ps.Status',
            'af.transaction_id',
            'af.user_hash',
            'af.weight',
            'ps.DateCreate'
        ])
//            ->from('antifraud_fissss as af')
            ->from('antifraud_finger_print as af')
            ->leftJoin('pay_schet as ps', 'ps.ID = af.transaction_id')
            ->leftJoin('uslugatovar AS qp', 'ps.IdUsluga = qp.ID')
            ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => strtotime($this->datefrom . ":00"),
                ':DATETO' => strtotime($this->dateto . ":59")
            ]);

        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        if ($IdPart > 0) {
            $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        if (count($this->status) > 0) {
            $query->andWhere(['in', 'ps.Status', $this->status]);
        }
        if (count($this->usluga) > 0) {
            $query->andWhere(['in', 'ps.IdUsluga', $this->usluga]);
        }
        if (count($this->TypeUslug) > 0) {
            $query->andWhere(['in', 'qp.IsCustom', $this->TypeUslug]);
        }
        if ($this->id > 0) {
            $query->andWhere('ps.ID = :ID', [':ID' => $this->id]);
        }
        if (!empty($this->Extid)) {
            $query->andWhere('ps.Extid = :EXTID', [':EXTID' => $this->Extid]);
        }
        if ($this->summpay > 0) {
            $query->andWhere('ps.SummPay = :SUMPAY', [':SUMPAY' => round($this->summpay * 100.0)]);
        }

        $query->orderBy('ps.ID desc');

        if (!$nolimit) {
            $query->orderBy('`ID` DESC')->limit(100);
        }
        return $query->all();
    }

    public function getDataProviderList($IsAdmin, $page = 0, $nolimit = 0): ArrayDataProvider
    {
        $list = $this->getList($IsAdmin, $page, $nolimit);
        return new ArrayDataProvider([
            'allModels' => $list,
            'pagination' => [
                'pageSize' => 10,
                'page'=>$page
            ]
        ]);
    }
}