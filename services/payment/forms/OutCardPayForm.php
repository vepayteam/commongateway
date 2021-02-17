<?php


namespace app\services\payment\forms;


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

    public function rules()
    {
        return [
            [['amount'], 'required'],
            [['cardnum'], 'match', 'pattern' => '/^\d{16}|\d{18}$/'],
            ['card', 'validateCard'],
        ];
    }

    public function validateCard()
    {
        if(empty($this->cardnum) && empty($this->card)) {
            $this->addError('card', 'Ид карты или номер карты обязательны к заполнению');
        }

        if($this->card > 0 && !empty($this->getCardOut())) {
            $this->addError('card', 'empty card');
        }
    }

    /**
     * @return Cards|array|\yii\db\ActiveRecord|null
     */
    public function getCardOut()
    {
        if(!$this->_card) {
            $this->_card = Cards::find()
                ->withPartner($this->partner)
                ->andWhere(['=', Cards::tableName() . '.ID', $this->card])
                ->one();
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

}
