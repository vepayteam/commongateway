<?php

namespace app\models\partner\stat;

use app\models\partner\UserLk;
use Yii;
use yii\base\Model;
use yii\db\Query;

class AutopayStat extends Model
{
    public $usluga = [];
    public $IdPart;
    public $datefrom;
    public $dateto;

    public function rules()
    {
        return [
            [['IdPart'], 'integer'],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y'],
            [['datefrom', 'dateto'], 'required'],
            [['usluga'], 'each', 'rule' => ['string']]
        ];
    }

    public function afterValidate()
    {
        //после валидации - преобразуем данные в int - для запроса в бд.
        foreach ($this->usluga as $key => $val){
            $this->usluga[$key] = (int)$val;
        }
        parent::afterValidate();
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    public function GetData($IsAdmin)
    {
        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $datefrom = strtotime($this->datefrom);
        $dateto = strtotime($this->dateto);

        $cntcards = $activecards = $reqcards = $payscards = 0;

        //видеть общее количество привязанных карт
        $query = new Query();
        $query
            ->select(['c.ID'])
            ->from('`cards` AS c')
            ->leftJoin('user AS u', 'u.ID = c.IdUser')
            ->where(['c.TypeCard' => 0]);
        if ($IdPart > 0) {
            $query->andWhere('u.ExtOrg = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $cntcards = $query->count();

        //сколько активных привязанных карт
        $query = new Query();
        $query
            ->select(['c.ID'])
            ->from('`cards` AS c')
            ->leftJoin('`pay_schet` AS ps', 'ps.IdKard = c.ID')
            ->leftJoin('user AS u', 'u.ID = c.IdUser')
            ->where(['between', 'ps.DateCreate', $datefrom, $dateto])
            ->andWhere(['c.TypeCard' => 0])
            ->andWhere(['ps.IsAutoPay' => 1]);
        if ($IdPart > 0) {
            $query->andWhere('u.ExtOrg = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $query->groupBy('c.ID');
        $activecards = $query->count();

        //сколько запросов идет к одной карте/всего
        $query = new Query();
        $query
            ->select(['ps.ID'])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`cards` AS c', 'ps.IdKard = c.ID')
            ->leftJoin('user AS u', 'u.ID = c.IdUser')
            ->where(['between', 'ps.DateCreate', $datefrom, $dateto])
            ->andWhere(['c.TypeCard' => 0])
            ->andWhere(['ps.IsAutoPay' => 1]);
        if ($IdPart > 0) {
            $query->andWhere('u.ExtOrg = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $reqcards = $query->count();

        //Сколько успешных запросов
        $query = new Query();
        $query
            ->select(['ps.ID'])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`cards` AS c', 'ps.IdKard = c.ID')
            ->leftJoin('user AS u', 'u.ID = c.IdUser')
            ->where(['between', 'ps.DateCreate', $datefrom, $dateto])
            ->andWhere(['c.TypeCard' => 0])
            ->andWhere(['ps.IsAutoPay' => 1, 'ps.Status' => 1]);
        if ($IdPart > 0) {
            $query->andWhere('u.ExtOrg = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $payscards = $query->count();

        return [
            'cntcards' => $cntcards,
            'activecards' => $activecards,
            'reqcards' => $reqcards,
            'payscards' => $payscards
        ];
    }

}