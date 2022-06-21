<?php

namespace app\models\payonline;

use app\services\payment\forms\CreatePayForm;
use Yii;
use yii\base\Model;

/**
 * @deprecated Use {@see CreatePayForm} instead.
 */
class PayForm extends Model
{
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
    //public $SumPay;
    //public $ComisPay;

    public function rules()
    {
        return [
            [['CardNumber'], 'match', 'pattern' => '/^\d{16}|\d{18}$/', 'message' => \Yii::t('app.payment-errors', 'Неверный номер карты')],
            ['CardNumber', function ($attribute, $params) {
                if ($this->CardNumber) {
                    if (preg_match('/^\d{16}|\d{18}$/', $this->CardNumber) && !Cards::CheckValidCard($this->CardNumber)) {
                        $this->addError($attribute, \Yii::t('app.payment-errors', 'Неверный номер карты'));
                    }
                }
            }],
            [['CardHolder'], 'match', 'pattern' => '/^[\w\s]{3,80}$/',  'message' => \Yii::t('app.payment-errors', 'Неверные Фамилия Имя держателя карты')],
            [['CardExp'], 'match', 'pattern' => '/^[01]\d{3}$/', 'message' => \Yii::t('app.payment-errors', 'Неверный Срок действия')],
            ['CardExp', function ($attribute, $params) {
                if ($this->CardExp) {
                    $CardMonth = substr($this->CardExp, 0, 2);
                    $CardYear = substr($this->CardExp, 2, 2);
                    if (!preg_match('/^[01]\d{3}$/', $this->CardExp) ||
                        $CardMonth < 1 ||
                        $CardMonth > 12 ||
                        // TODO: https://it.dengisrazy.ru/browse/VPBC-1468
                        (in_array(Cards::GetCardBrand($this->CardNumber), [
                                Cards::BRAND_AMERICAN_EXPRESS,
                                Cards::BRAND_MAESTRO,
                                Cards::BRAND_MASTERCARD
                            ]) &&
                            ($CardYear + 2000 < date('Y') ||
                                ($CardYear + 2000 == date('Y') && $CardMonth < date('n')))
                        ) ||
                        $CardYear + 2000 > date('Y') + 10
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
            [['CardNumber', 'CardHolder', 'CardExp', 'CardCVC', 'IdPay'], 'required', 'message' => \Yii::t('app.payment-errors', 'Заполните данные карты')]
        ];
    }

    public function attributeLabels()
    {
        return [
            'CardNumber' => Yii::t('app.payment-form', 'Номер карты'),
            'CardHolder' => Yii::t('app.payment-form', 'Владелец'),
            'CardExp' => Yii::t('app.payment-form', 'Действует'),
            'CardCVC' => Yii::t('app.payment-form', 'CVC'),
            'Phone' => Yii::t('app.payment-form', 'Номер телефона'),
            'LinkPhone' => Yii::t('app.payment-form', 'Привязать номер к карте'),
            'Email' => Yii::t('app.payment-form', 'Почта для отправления чека')
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
     * URL завершения оплаты по PCIDSS
     *
     * @param $id
     * @return string
     */
    public function GetRetUrl($id)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/pay/orderdone?id='. $id;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://'.$_SERVER['SERVER_NAME'].'/pay/orderdone?id='.$id;
        } else {
            return 'https://api.vepay.online/pay/orderdone?id='.$id;
        }
    }

    public function GetWidgetRetUrl($id)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            return 'http://'.$_SERVER['SERVER_NAME'].'/widget/orderdone?id='.$id;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://'.$_SERVER['SERVER_NAME'].'/widget/orderdone?id='.$id;
        } else {
            return 'https://api.vepay.online/widget/orderdone?id='.$id;
        }
    }

}
