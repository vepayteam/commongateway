<?php


namespace app\services\payment\forms;


use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\User;
use app\services\payment\models\PaySchet;
use yii\base\Model;
use yii\db\Query;

class AutoPayForm extends Model
{
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

    public function rules()
    {
        return [
            [['amount', 'card'], 'required'],
            [['amount'], 'number', 'min' => 1, 'max' => 1000000],
            [['extid'], 'string', 'max' => 40],
            [['document_id'], 'string', 'max' => 40],
            [['fullname'], 'string', 'max' => 80],
            [['postbackurl'], 'url'],
            [['postbackurl'], 'string', 'max' => 300],
            [['descript'], 'string', 'max' => 200],
            [['card'], 'integer'],
            [['card'], 'validateCard'],
        ];
    }

    public function validateCard()
    {
        if(!$this->getCard()) {
            $this->addError('card', 'Нет такой карты');
        }
    }

    public function getError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * @param Partner $partner
     * @param int $type
     * @return Cards|null
     */
    public function getCard()
    {
        if(!$this->_card) {
            $query = (new Query())
                ->select(['c.`ID` AS IdCard', 'u.`ID` AS IdUser'])
                ->from('cards AS c')
                ->leftJoin('user AS u', 'c.`IdUser`=u.`ID`')
                ->where([
                    'u.ExtOrg' => $this->partner->ID,
                    'c.IsDeleted' => 0,
                    'c.TypeCard' => 0,
                    'c.ID' => $this->card
                ]);
            $res = $query->one();
            if ($res && $res['IdCard'] && $res['IdUser']) {
                $this->user = User::findOne(['ID' => $res['IdUser']]);
                $this->_card = Cards::findOne(['ID' => $res['IdCard'], 'TypeCard' => 0, 'IsDeleted' => 0]);
            }
        }

        return $this->_card;
    }
}
