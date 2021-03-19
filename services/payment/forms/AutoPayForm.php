<?php


namespace app\services\payment\forms;


use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\User;
use app\models\traits\ValidateFormTrait;
use app\services\payment\models\PaySchet;
use Serializable;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\Json;

class AutoPayForm extends Model implements Serializable
{
    use ValidateFormTrait;

    /** @var Cards */
    protected $_card;
    /** @var Partner */
    public $partner;
    /** @var PaySchet  */
    public $paySchet;
    /** @var User */
    public $user;

    public $amount = 0;
    public $document_id = '';
    public $fullname = '';
    public $extid = '';
    public $descript = '';

    public $card = 0;
    public $timeout = 30;
    public $postbackurl = '';
    public $postbackurl_v2 = '';

    public function rules()
    {
        return [
            [['amount', 'card'], 'required'],
            [['amount'], 'number', 'min' => 1, 'max' => 1000000],
            [['extid'], 'string', 'max' => 40],
            [['document_id'], 'string', 'max' => 40],
            [['fullname'], 'string', 'max' => 80],
            [['postbackurl', 'postbackurl_v2'], 'url'],
            [['postbackurl', 'postbackurl_v2'], 'string', 'max' => 300],
            [['descript'], 'string', 'max' => 200],
            [['card'], 'integer'],
            [['card'], 'validateCard'],
        ];
    }

    public function validateCard()
    {
        $card = $this->getCard();
        if(!$card) {
            $this->addError('card', 'Нет такой карты');
        }

        if($card && empty($card->panToken->EncryptedPAN)) {
            $this->addError('card', 'Карта просрочена');
        }
    }

    /**
     * @param Partner $partner
     * @param int $type
     * @return Cards|null
     */
    public function getCard()
    {
        if(!$this->_card) {
            $card = Cards::findOne(['ID' => $this->card]);

            if($card && $card->user && $card->user->ExtOrg == $this->partner->ID) {
                $this->user = $card->user;
                $this->_card = $card;
            }
        }

        return $this->_card;
    }

    /**
     * @return string
     */
    public function getMutexKey()
    {
        return 'autoPay' . $this->partner->ID . $this->extid;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        $arr = [
            'amount' => $this->amount,
            'extid' => $this->extid,
            'document_id' => $this->document_id,
            'fullname' => $this->fullname,
            'postbackurl' => $this->postbackurl,
            'descript' => $this->descript,
            'card' => $this->card,
            'partnerId' => $this->partner ? $this->partner->ID : null,
            'paySchetId' => $this->paySchet ? $this->paySchet->ID : null,
            'userId' => $this->user ? $this->user->ID : null,
        ];

        return Json::encode($arr);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $arr = Json::decode($serialized, true);

        $this->amount = $arr['amount'];
        $this->extid = $arr['extid'];
        $this->document_id = $arr['document_id'];
        $this->fullname = $arr['fullname'];
        $this->postbackurl = $arr['postbackurl'];
        $this->descript = $arr['descript'];
        $this->card = $arr['card'];

        if(!is_null($arr['partnerId'])) {
            $this->partner = Partner::findOne(['ID' => $arr['partnerId']]);
        }

        if(!is_null($arr['paySchetId'])) {
            $this->paySchet = PaySchet::findOne(['ID' => $arr['paySchetId']]);
        }

        if(!is_null($arr['userId'])) {
            $this->user = User::findOne(['ID' => $arr['userId']]);
        }
    }
}
