<?php

namespace app\services\payment\forms;

use app\services\LanguageService;
use app\services\payment\interfaces\AmountFormInterface;
use app\services\payment\models\Currency;
use app\services\payment\models\repositories\CurrencyRepository;
use yii\validators\EmailValidator;
use yii\validators\StringValidator;

class MerchantPayForm extends BaseForm implements AmountFormInterface
{
    public $type;
    public $amount = 0;
    public $currency = Currency::MAIN_CURRENCY;
    public $document_id = '';
    public $fullname = '';
    public $extid = '';
    public $descript = '';
    public $id;
    //public $type = 0;/*'type', */
    public $card = 0;
    public $timeout = 30;
    public $successurl = '';
    public $failurl = '';
    public $cancelurl = '';
    public $postbackurl = '';
    public $postbackurl_v2 = '';
    public $client;
    /**
     * @var int Значения: 1 или 0. Если 1, производится регистрация карты на рекуррентные платежи и выплаты.
     */
    public $regcard = 0;
    public $language;

    public const REQUIRED = 'required';
    public const NOT_SUPPORTED = 'not supported';

    // Client request attributes
    public const CLIENT_FIELDS = [
        'email' => 'required',
        'address' => '',
        'login' => '',
        'phone' => '',
        'zip' => ''
    ];

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['type'], 'integer', 'min' => 0],
            [['amount'], 'number', 'min' => 1, 'max' => 1000000],
            [['extid'], 'string', 'max' => 40],
            [['document_id'], 'string', 'max' => 40],
            [['fullname'], 'string', 'max' => 80],
            [['successurl', 'failurl', 'cancelurl', 'postbackurl', 'postbackurl_v2'], 'url'],
            [['successurl', 'failurl', 'cancelurl'], 'string', 'max' => 1000],
            [['postbackurl', 'postbackurl_v2'], 'string', 'max' => 255],
            [['descript'], 'string', 'max' => 200],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59],
            [['amount', 'currency'], 'required'],
            [['amount', 'card'], 'required'],
            [['currency'], 'validateCurrency'],
            [['regcard'], 'in', 'range' => [1, 0], 'strict' => true],
            [['language'], 'in', 'range' => LanguageService::ALL_API_LANG_LIST],
        ];
    }

    public function validateCurrency()
    {
        $db = new CurrencyRepository();
        $currencyKey = 'currency';
        $validator = $this->getClientValidator($currencyKey);

        if (!$validator->validate($this->currency)) {
            $this->setError($currencyKey);
            return;
        }
        if (!$db->hasCurrency($this->currency)) {
            $this->setError($currencyKey, self::NOT_SUPPORTED);
        }
    }

    /**
     * add custom client arrays attributes validation
     * @param $key
     * @return EmailValidator|StringValidator
     */
    public function getClientValidator($key)
    {
        $emailValidator = new EmailValidator();
        $stringValidator = new StringValidator();
        switch ($key) {
            case 'currency':
                $stringValidator->max = 3;
                return $stringValidator;
            case 'email':
                return $emailValidator;
            case 'address':
                $stringValidator->max = 255;
                return $stringValidator;
            case 'phone':
            case 'login':
                $stringValidator->max = 32;
                return $stringValidator;
            case 'zip':
                $stringValidator->max = 16;
                return $stringValidator;
            default:
                return $stringValidator;
        }
    }

    /**
     * @param string $key
     * @param string $state
     */
    public function setError(string $key, string $state = 'invalid')
    {
        $this->addError(
            $key,
            sprintf(
                'Attribute %s is %s',
                $key,
                $state
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
