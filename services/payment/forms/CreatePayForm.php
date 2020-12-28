<?php


namespace app\services\payment\forms;


use app\models\payonline\Cards;
use app\services\payment\models\PaySchet;
use Yii;
use yii\base\Model;

class CreatePayForm extends Model
{
    /** @var PaySchet */
    protected $paySchet;

    public $CardNumber;
    public $CardHolder;
    public $CardExp;
    public $CardYear = '';
    public $CardMonth = '';
    public $CardCVC;
    public $Phone = '';
    public $Email = '';
    public $LinkPhone = false;

    public $IdPay;

    public function rules()
    {
        return [
            [['CardNumber'], 'match', 'pattern' => '/^\d{16}|\d{18}$/', 'message' => 'Неверный номер карты'],
            ['CardNumber', 'validateIsTestCard'],
            ['CardNumber', function ($attribute, $params) {
                if ($this->CardNumber) {
                    if (preg_match('/^\d{16}|\d{18}$/', $this->CardNumber) && !Cards::CheckValidCard($this->CardNumber)) {
                        $this->addError($attribute, 'Неверный номер карты');
                    }
                }
            }],
            [['CardHolder'], 'match', 'pattern' => '/^[\w\s]{3,80}$/',  'message' => 'Неверные Фамилия Имя держателя карты'],
            [['CardExp'], 'match', 'pattern' => '/^[01]\d{3}$/', 'message' => 'Неверный Срок действия'],
            ['CardExp', function ($attribute, $params) {
                if ($this->CardExp) {
                    $CardMonth = substr($this->CardExp, 0, 2);
                    $CardYear = substr($this->CardExp, 2, 2);
                    if (!preg_match('/^[01]\d{3}$/', $this->CardExp) ||
                        $CardMonth < 1 ||
                        $CardMonth > 12 ||
                        $CardYear + 2000 < date('Y') ||
                        ($CardYear + 2000 == date('Y') && $CardMonth < date('n')) ||
                        $CardYear + 2000 > date('Y') + 10
                    ) {
                        $this->addError($attribute, 'Неверный Срок действия');
                    }
                }
            }],
            [['CardCVC'], 'match', 'pattern' => '/^\d{3}$/', 'message' => 'Неверный CVC код'],
            [['IdPay'], 'integer', 'min' => 1],
            [['Phone'], 'match', 'pattern' => '/^\d{10}$/', 'message' => 'Неверный номер телефона'],
            [['Email'], 'email', 'message' => 'Неверный адрес почты'],
            [['LinkPhone'], 'boolean'],
            [['CardNumber', 'CardHolder', 'CardExp', 'CardCVC', 'IdPay'], 'required', 'message' => 'Заполните данные карты']
        ];
    }

    public function validateIsTestCard()
    {
        if(Yii::$app->params['TESTMODE'] === 'Y' && !in_array($this->CardNumber, Yii::$app->params['testCards'])) {
            $this->addError('CardNumber', 'На тестовом контуре допускается использовать только тестовые карты');
        }
    }

    public function attributeLabels()
    {
        return [
            'CardNumber' => 'Номер карты',
            'CardHolder' => 'Владелец',
            'CardExp' => 'Действует',
            'CardCVC' => 'CVC',
            'Phone' => 'Номер телефона',
            'LinkPhone' => 'Привязать номер к карте',
            'Email' => 'Почта для отправления чека'
        ];
    }

    public function afterValidate()
    {
        $this->CardMonth = substr($this->CardExp, 0, 2);
        $this->CardYear = substr($this->CardExp, 2, 2);

        parent::afterValidate();
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * @return PaySchet
     */
    public function getPaySchet()
    {
        if(!$this->paySchet) {
            $this->paySchet = PaySchet::findOne(['ID' => $this->IdPay]);
        }
        return $this->paySchet;
    }

    /**
     * URL завершения оплаты по PCIDSS
     *
     * @param $id
     * @return string
     */
    public function getReturnUrl()
    {
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/pay/orderdone?id='. $this->IdPay;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://'.$_SERVER['SERVER_NAME'].'/pay/orderdone?id=' . $this->IdPay;
        } else {
            return 'https://api.vepay.online/pay/orderdone?id=' . $this->IdPay;
        }
    }

}
