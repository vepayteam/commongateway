<?php


namespace app\services\card\base;

use Yii;
use app\models\mfo\MfoReq;
use app\models\payonline\Cards;
use app\models\payonline\User;
use app\services\base\Service;
use yii\db\Query;

class CardBase extends Service
{

    public $mfo;
    public $id;
    public $card;
    public $type;

    public $extid = '';
    public $timeout = 15;
    public $successurl = '';
    public $failurl = '';
    public $cancelurl = '';

    /* @var null|User */
    public $user = null;

    public function init()
    {
        $this->mfoReg();
    }

    public function rules()
    {
        return [];
    }

    private function mfoReg():void
    {
        $this->mfo = new MfoReq();
        $this->mfo->LoadData(Yii::$app->request->getRawBody());
    }

    public function loadProperties(array $params = []): void
    {
        $this->load($this->mfo->Req(), '');
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * Получить карту
     * @param $IdPartner
     * @param $type
     * @return Cards|null
     * @throws \yii\db\Exception
     */
    public function FindKard($IdPartner, $type=-1)
    {
        $query = (new Query())
            ->select(['c.`ID` AS IdCard', 'u.`ID` AS IdUser'])
            ->from('cards AS c')
            ->leftJoin('user AS u', 'c.`IdUser`=u.`ID`')
            ->where([
                'u.ExtOrg' => $IdPartner,
                'c.IsDeleted' => 0,
                'c.ID' => $this->card
            ]);
        if ($type != -1) {
            $query->andWhere(['c.TypeCard' => $type]);
        }
        $res = $query->one();
        if ($res && $res['IdCard'] && $res['IdUser']) {
            $this->user = User::findOne(['ID' => $res['IdUser']]);
            if ($type != -1) {
                return Cards::findOne(['ID' => $res['IdCard'], 'TypeCard' => $type, 'IsDeleted' => 0]);
            } else {
                return Cards::findOne(['ID' => $res['IdCard'], 'IsDeleted' => 0]);
            }
        }

        return null;
    }

    /**
     * @param $IdPartner
     * @param $type
     * @return Cards|null
     * @throws \yii\db\Exception
     */
    public function FindKardByPay($IdPartner, $type)
    {
        $IdCard = Yii::$app->db->createCommand('
            SELECT IdKard FROM `pay_schet` WHERE `ID` = :IDPAY 
        ', [':IDPAY' => $this->id])->queryScalar();
        if ($IdCard) {
            $this->card = $IdCard;
            return $this->FindKard($IdPartner, $type);
        }

        return null;
    }

    public function GetPayState()
    {
        $payState = Yii::$app->db->createCommand('
            SELECT 
                `Status`
            FROM `pay_schet` 
            WHERE `ID` = :IDPAY 
        ', [':IDPAY' => $this->id])->queryScalar();

        return (int)$payState;
    }

    public function GetRegForm($IdPay)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            return 'http://127.0.0.1:806/pay/form/' . $IdPay;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://'.$_SERVER['SERVER_NAME'].'/pay/form/' . $IdPay;
        } else {
            return 'https://api.vepay.online/pay/form/' . $IdPay;
        }
    }
}