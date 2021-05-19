<?php

namespace app\services\payment\forms;

use app\services\payment\interfaces\AmountFormInterface;
use yii\validators\EmailValidator;

class MerchantPayForm extends BaseForm implements AmountFormInterface
{
    public $type;
    public $amount = 0;
    public $document_id = '';
    public $fullname = '';
    public $extid = '';
    public $descript = '';
    public $id;
    //public $type = 0;/*'type', */
    public $card = 0;
    public $timeout = 15;
    public $successurl = '';
    public $failurl = '';
    public $cancelurl = '';
    public $postbackurl = '';
    public $postbackurl_v2 = '';
    public $client;
    private const REQUIRED = 'required';
    // Add Client request required attributes
    private const CLIENT_REQUIRED_RULES = [
        'email'
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
            [['successurl', 'failurl', 'cancelurl', 'postbackurl', 'postbackurl_v2'], 'string', 'max' => 300],
            [['descript'], 'string', 'max' => 200],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59],
            [['amount'], 'required'],
            [['amount', 'card'], 'required'],
            [['client'], 'validateClient'],
        ];
    }

    public function validateClient()
    {
        if (!is_array($this->client)) {
            return;
        }
        foreach (self::CLIENT_REQUIRED_RULES as $requiredRule) {
            if (!array_key_exists($requiredRule, $this->client)) {
                $this->setError($requiredRule, self::REQUIRED);
            }
        }
        foreach ($this->client as $key => $clientData) {
            $emailValidator = new EmailValidator();
            if ($key === 'email' && !$emailValidator->validate($clientData)) {
                $this->setError($key);
            }
            // add custom client arrays attributes validation
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
