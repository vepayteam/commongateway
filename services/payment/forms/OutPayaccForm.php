<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use app\services\ident\traits\ErrorModelTrait;
use yii\base\Model;

class OutPayaccForm extends Model
{
    use ErrorModelTrait;

    /** @var Partner */
    public $partner;
    public $fio;
    public $account;
    public $bic;
    public $descript;
    public $amount;
    public $sms;


    public function rules()
    {
        return [
            [['fio', 'account', 'bic', 'descript', 'amount'], 'required'],
            [['account'], 'match', 'pattern' => '/^\d{20}$/'],
            [['bic'], 'match', 'pattern' => '/^\d{9}$/'],
            [['descript'], 'string', 'max' => 210],
            ['descript', 'filter', 'filter' => function ($value) {
                return str_replace(" ", " ", $value);
            }],
            [['fio'], 'string', 'max' => 150],
            [['amount'], 'number', 'min' => 1, 'max' => 21000000],
            [['sms'], 'integer'],
        ];
    }

}
