<?php


namespace app\services\balance\models;


use app\models\payonline\Partner;
use yii\base\Model;

class PartsBalanceForm extends Model
{
    private $partner;

    public $datefrom;
    public $dateto;
    public $partnerId;
    public $sort;

    public function rules()
    {
        return [
            [['datefrom', 'dateto', 'partnerId'], 'required'],
            ['partnerId', 'validatePartner']
        ];
    }

    /**
     * @return bool
     */
    public function validatePartner()
    {
        return !empty($this->getPartner());
    }

    /**
     * @return Partner|null
     */
    public function getPartner()
    {
        if(!$this->partner) {
            $this->partner = Partner::findOne(['ID' => $this->partnerId]);
        }
        return $this->partner;
    }

}
