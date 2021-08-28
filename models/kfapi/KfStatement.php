<?php


namespace app\models\kfapi;

use app\models\payonline\Partner;
use yii\base\Model;

class KfStatement extends Model
{
    /** @var Partner */
    public $partner;

    public $account;
    public $datefrom;
    public $dateto;

    public $dayfrom;
    public $dayto;
    public $typeAcc = 0;
    public $sort = 0;

    public function rules()
    {
        return [
            [['account'], 'string', 'length' => [20]],
            [['datefrom', 'dateto'], 'string', 'max' => 30],
            [['account', 'datefrom', 'dateto'], 'required'],
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    public function afterValidate()
    {
        $this->dayfrom = date("Y-m-d\TH:i:s", strtotime($this->datefrom));
        $this->dayto = date("Y-m-d\TH:i:s", strtotime($this->dateto));

        parent::afterValidate();
    }

}
