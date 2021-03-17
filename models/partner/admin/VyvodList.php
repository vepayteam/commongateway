<?php


namespace app\models\partner\admin;

use app\models\partner\UserLk;
use Yii;
use yii\base\Model;
use yii\db\Query;

class VyvodList extends Model
{
    /** @var int perevod na vydachu (на выплату) */
    const TYPE_USLUG_NA_VYDACHU = 0;
    /** @var int perechislene na schet (на р/с) */
    const TYPE_USLUG_NA_R_S_SCHET = 1;

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
    public function GetList(bool $IsAdmin, $type)
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
                'v.SumOp AS Summ',
                'ps.QrParams',
                'v.TypePerechisl'
            ])
            ->from('`vyvod_reestr` AS v')
            ->leftJoin('`partner` AS r', 'r.ID = v.IdPartner')
            ->leftJoin('`pay_schet` AS ps', 'ps.ID = v.IdPay')
            ->where('v.DateFrom >= :DATEFROM AND DateTo <= :DATETO', [
                ':DATEFROM' => $dateFrom,
                ':DATETO' => $dateTo
            ])
            ->orWhere(['BETWEEN', 'v.DateOp', $dateFrom, $dateTo])
            ->andWhere(['TypePerechisl' => $type]);

        if ($IdPart > 0) {
            $query->andWhere('v.IdPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        $query->orderBy(['v.DateOp' => SORT_DESC]);

        return $query->all();
    }

}