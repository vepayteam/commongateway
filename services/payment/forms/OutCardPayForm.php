<?php


namespace app\services\payment\forms;


use app\models\crypt\CardToken;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\traits\ValidateFormTrait;
use app\services\payment\models\PaySchet;
use yii\base\Model;

class OutCardPayForm extends Model
{
    use ValidateFormTrait;

    /** @var Partner */
    public $partner;
    /** @var PaySchet */
    public $paySchet;
    /** @var Cards */
    private $_card;


    public $amount = 0;
    public $extid = '';
    public $document_id = '';
    public $fullname = '';

    public $card = 0;
    public $cardnum;

    public $birthDate;
    public $countryOfCitizenship;
    public $countryOfResidence;
    public $documentType;
    public $documentIssuer;
    public $documentIssuedAt;
    public $documentValidUntil;
    public $birthPlace;
    public $documentSeries;
    public $documentNumber;
    public $phone;

    public function rules()
    {
        return [
            [['amount'], 'required'],
            [['cardnum'], 'match', 'pattern' => '/^\d{16}|\d{18}$/'],
            ['birthDate', 'match', 'pattern' => '/^[0-3][0-9]\.[0-1][0-9]\.[1-2][0-9]{3}$/i'],
            ['countryOfCitizenship', 'default', 'value' => 'RU'],
            ['countryOfResidence', 'default', 'value' => 'RU'],

            ['documentType', 'default', 'value' => 'PASSPORT'],
            ['documentIssuedAt', 'match', 'pattern' => '/^[0-3][0-9]\.[0-1][0-9]\.[1-2][0-9]{3}$/i'],
            ['documentValidUntil', 'match', 'pattern' => '/^[0-3][0-9]\.[0-1][0-9]\.[1-2][0-9]{3}$/i'],


            [[
                'fullname',
                'document_id',
                'extid',

                'birthPlace',
                'documentIssuer',
                'documentSeries',
                'documentNumber',
                'phone',
            ], 'safe'],
            ['card', 'validateCard'],
        ];
    }

    public function validateCard()
    {
        if(empty($this->cardnum) && empty($this->card)) {
            $this->addError('card', 'Ид карты или номер карты обязательны к заполнению');
        }

        if($this->card > 0 && empty($this->getCardOut())) {
            $this->addError('card', 'empty card');
        }
    }

    /**
     * @return Cards|array|\yii\db\ActiveRecord|null
     */
    public function getCardOut()
    {
        if(!$this->_card) {
            $q = Cards::find()
                ->withPartner($this->partner)
                ->andWhere(['=', Cards::tableName() . '.ID', $this->card]);
            $this->_card = $q->one();
            if($this->_card) {
                $CardToken = new CardToken();
                $this->cardnum = $CardToken->GetCardByToken($this->_card->IdPan);
            }
        }
        return $this->_card;
    }

    /**
     * @return string
     */
    public function getMutexKey()
    {
        return 'OutCardPay_' . $this->partner->ID . '_' . $this->extid;
    }

    public function getFirstName()
    {
        if(empty($this->fullname) || explode(' ', $this->fullname) < 2) {
            return 'БЕЗИМЕНИ';
        }
        return explode(' ', $this->fullname)[1];
    }

    public function getLastName()
    {
        if(empty($this->fullname) || explode(' ', $this->fullname) < 2) {
            return 'БЕЗИМЕНИ';
        }
        return explode(' ', $this->fullname)[0];
    }

}
