<?php


namespace app\models\partner\admin;

use app\models\partner\UserLk;
use Yii;
use yii\base\Model;
use yii\db\Query;

class SystemVoznagList extends Model
{
    public $datefrom;
    public $dateto;
    public $IdPart;
    public $TypeUslug = 0; //0 - все 1 - погашение 2 - выдача

    public function rules()
    {
        return [
            [['IdPart', 'TypeUslug'], 'integer'],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['datefrom', 'dateto'], 'required'],
        ];
    }


    /**
     * @param bool $IsAdmin
     * @return array
     */
    public function GetList(bool $IsAdmin)
    {
        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $dateFrom = strtotime($this->datefrom.":00");
        $dateTo = strtotime($this->dateto.":59");

        $query = new Query();
        $query
            ->select([
                'r.Name AS NamePartner',
                'v.DateOp',
                'v.DateFrom',
                'v.DateTo',
                'v.Summ',
                'v.TypeVyvod',
                'ps.QrParams'
            ])
            ->from('`vyvod_system` AS v')
            ->leftJoin('`partner` AS r', 'r.ID = v.IdPartner')
            ->leftJoin('`pay_schet` AS ps', 'ps.ID = v.IdPay')
            ->where('v.DateFrom >= :DATEFROM AND DateTo <= :DATETO', [
                ':DATEFROM' => $dateFrom,
                ':DATETO' => $dateTo
            ])
            ->orWhere(['BETWEEN', 'v.DateOp', $dateFrom, $dateTo]);

        if ($IdPart > 0) {
            $query->andWhere('v.IdPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $query->orderBy(['v.DateOp' => SORT_DESC]);

        return $query->all();
    }

}