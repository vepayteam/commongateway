<?php


namespace app\services\payment\forms;


use app\models\payonline\Cards;
use app\services\payment\models\PaySchet;
use Yii;
use yii\base\Model;

/**
 * @property-read CreatePayBrowserDataForm $browserDataForm {@see CreatePayForm::getBrowserDataForm()}
 *
 * @todo Lower class properties first letter. $CardNumber -> $cardNumber.
 */
class CreatePayForm extends Model
{
    /** @var CreatePayBrowserDataForm */
    private $_browserDataForm;

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
    /**
     * @var string|null JSON-encoded browser data.
     */
    public $browserDataJson;
    /**
     * @var string|null HTTP-header "Accept" from common (non ajax) HTTP-request to payment from.
     */
    public $httpHeaderAccept;

    public $IdPay;

    /**
     * {@inheritDoc}
     */
    public function formName(): string
    {
        return 'PayForm';
    }

    public function rules()
    {
        return [
            [['CardNumber'], 'match', 'pattern' => '/^\d{16}|\d{18}$/', 'message' => \Yii::t('app.payment-errors', 'Неверный номер карты')],
            ['CardNumber', 'validateIsTestCard'],
            ['CardNumber', function ($attribute, $params) {
                if ($this->CardNumber) {
                    if (preg_match('/^\d{16}|\d{18}$/', $this->CardNumber) && !Cards::CheckValidCard($this->CardNumber)) {
                        $this->addError($attribute, \Yii::t('app.payment-errors', 'Неверный номер карты'));
                    }
                }
            }],
            [['CardHolder'], 'match', 'pattern' => '/^[\w\s]{3,80}$/', 'message' => \Yii::t('app.payment-errors', 'Неверные Фамилия Имя держателя карты')],
            [['CardExp'], 'match', 'pattern' => '/^[01]\d{3}$/', 'message' => \Yii::t('app.payment-errors', 'Неверный Срок действия')],
            ['CardExp', function ($attribute, $params) {
                if ($this->CardExp) {
                    $CardMonth = substr($this->CardExp, 0, 2);
                    $CardYear = substr($this->CardExp, 2, 2);
                    if (!preg_match('/^[01]\d{3}$/', $this->CardExp)
                        || $CardMonth < 1
                        || $CardMonth > 12
                        // TODO: Убрать после потери актуальности https://it.dengisrazy.ru/browse/VPBC-1468
                        || (!in_array(Cards::GetTypeCard($this->CardNumber), [
                                Cards::BRAND_AMERICAN_EXPRESS,
                                Cards::BRAND_MAESTRO,
                                Cards::BRAND_MASTERCARD,
                                Cards::BRAND_VISA
                            ])
                            && ($CardYear + 2000 < date('Y')
                                || ($CardYear + 2000 == date('Y') && $CardMonth < date('n')))
                        )
                        || $CardYear + 2000 > date('Y') + 10
                    ) {
                        $this->addError($attribute, \Yii::t('app.payment-errors', 'Неверный Срок действия'));
                    }
                }
            }],
            [['CardCVC'], 'match', 'pattern' => '/^\d{3}$/', 'message' => \Yii::t('app.payment-errors', 'Неверный CVC код')],
            [['IdPay'], 'integer', 'min' => 1],
            [['Phone'], 'match', 'pattern' => '/^\d{10}$/', 'message' => \Yii::t('app.payment-errors', 'Неверный номер телефона')],
            [['Email'], 'email', 'message' => \Yii::t('app.payment-errors', 'Неверный адрес почты')],
            [['LinkPhone'], 'boolean'],
            [['CardNumber', 'CardHolder', 'CardExp', 'CardCVC', 'IdPay'], 'required', 'message' => \Yii::t('app.payment-errors', 'Заполните данные карты')],

            [['httpHeaderAccept'], 'string', 'max' => 255],
            [['browserDataJson'], 'validateBrowserDataJson'],/** {@see validateBrowserDataJson()} */
        ];
    }

    public function validateIsTestCard()
    {
        if (Yii::$app->params['TESTMODE'] === 'Y' && !in_array($this->CardNumber, Yii::$app->params['testCards'])) {
            $this->addError('CardNumber', 'На тестовом контуре допускается использовать только тестовые карты');
        }
    }

    public function validateBrowserDataJson()
    {
        $browserData = json_decode($this->browserDataJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError('browserDataJson', 'Неправильный формат данных браузера.');
            return;
        }

        $this->browserDataForm->setAttributes($browserData);
        if (!$this->browserDataForm->validate()) {
            $errors = $this->browserDataForm->getFirstErrors();
            $this->addError('browserDataJson', 'Ошибка валидации в данных браузера: ' . reset($errors));
        }
    }

    public function getBrowserDataForm(): CreatePayBrowserDataForm
    {
        if ($this->_browserDataForm === null) {
            $this->_browserDataForm = new CreatePayBrowserDataForm();
        }
        return $this->_browserDataForm;
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
        if (!$this->paySchet) {
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
            return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/pay/orderdone?id=' . $this->IdPay;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://' . $_SERVER['SERVER_NAME'] . '/pay/orderdone?id=' . $this->IdPay;
        } else {
            return 'https://api.vepay.online/pay/orderdone?id=' . $this->IdPay;
        }
    }

    public function GetWidgetRetUrl($id)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            return 'http://' . $_SERVER['SERVER_NAME'] . '/widget/orderdone?id=' . $id;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://' . $_SERVER['SERVER_NAME'] . '/widget/orderdone?id=' . $id;
        } else {
            return 'https://api.vepay.online/widget/orderdone?id=' . $id;
        }
    }

}
