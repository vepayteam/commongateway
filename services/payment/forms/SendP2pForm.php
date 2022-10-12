<?php


namespace app\services\payment\forms;


use app\models\traits\ValidateFormTrait;
use app\services\payment\models\PaySchet;
use yii\base\Model;

class SendP2pForm extends Model
{
    use ValidateFormTrait;

    /** @var PaySchet */
    public $paySchet;

    public $amount;
    public $cardPan;
    public $cardExpMonth;
    public $cardExpYear;
    public $cvv;
    public $cardHolder;
    public $outCardPan;

    public function __construct(PaySchet $paySchet)
    {
        parent::__construct();

        $this->paySchet = $paySchet;
    }

    public function rules()
    {
        return [
            [['amount', 'cardPan', 'cardExpMonth', 'cardExpYear', 'cvv', 'cardHolder', 'outCardPan'], 'required'],
            [['amount'], 'integer', 'numberPattern' => '[0-9]{1,5}\,[0-9]{,2}'],
            [['cardPan'], 'integer', 'numberPattern' => '[0-9]{16}'],
            [['outCardPan'], 'integer', 'numberPattern' => '[0-9]{16}'],
            [['cardExpMonth'], 'integer', 'numberPattern' => '[0-9]{1,2}'],
            [['cardExpYear'], 'integer', 'numberPattern' => '[0-9]{4}'],
            [['cvv'], 'integer', 'numberPattern' => '[0-9]{3}'],
            [['cardHolder'], 'match', 'pattern' => '/[a-zA-Z\s]{3,150}/i'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'amount' => 'Суммы перевода',
        ];
    }
}
