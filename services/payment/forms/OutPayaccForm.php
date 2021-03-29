<?php


namespace app\services\payment\forms;


use app\models\payonline\Partner;
use app\services\ident\traits\ErrorModelTrait;
use app\services\payment\models\PaySchet;
use yii\base\Model;

class OutPayaccForm extends Model
{
    use ErrorModelTrait;

    /** @var PaySchet */
    public $paySchet;

    /** @var Partner */
    public $partner;
    public $extid;
    public $fio;
    public $account;
    public $bic;
    public $descript;
    public $amount;
    public $sms;

    public $inn;
    public $kpp;


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
            [['fio', 'extid'], 'string', 'max' => 150],
            [['amount'], 'number', 'min' => 1, 'max' => 21000000],
            [['sms'], 'integer'],
        ];
    }

}
