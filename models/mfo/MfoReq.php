<?php


namespace app\models\mfo;

use app\models\bank\TCBank;
use app\models\kfapi\CheckIP;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\User;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use Yii;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

class MfoReq
{
    /* @var $user User */
    public $user = null;

    public $mfo = 0;

    private $req = [];

    /**
     * @param $jsonReq
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\db\Exception
     */
    public function LoadData($jsonReq)
    {
        try {
            $this->req = Json::decode($jsonReq);
            //$this->req = json_decode($jsonReq, false, 512, JSON_BIGINT_AS_STRING);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e);
        }

        $login = Yii::$app->request->headers->get('X-Mfo');
        $token = Yii::$app->request->headers->get('X-Token');
        if (!empty($login) && !empty($token) && $this->checkMfoToken($jsonReq, $login, $token)) {
            $this->user = null;
        } else {
            throw new UnauthorizedHttpException();
        }

    }

    /**
     * Проверка доступа к АПИ
     *
     * @param $jsonReq
     * @param $login
     * @param $token
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     */
    private function checkMfoToken($jsonReq, $login, $token)
    {
        $res = Yii::$app->db->createCommand('
            SELECT 
                `PaaswordApi`, `ID`, `IpAccesApi`
            FROM 
                `partner` 
            WHERE 
                `IsDeleted` = 0 
                AND `IsBlocked` = 0
                AND `IsMfo` = 1
                AND `ID` = :IDMFO 
            LIMIT 1
        ', [':IDMFO' => $login]
        )->queryOne();
        if (!$res) {
            return false;
        }
        if (!empty($res['IpAccesApi'])) {
            $this->CheckIpAccess($res['IpAccesApi']);
        }
        if (Yii::$app->params['DEVMODE'] == 'Y' || sha1(sha1($res['PaaswordApi']) . sha1($jsonReq)) == $token) {
            $this->mfo = $res['ID'];
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверка доступа по IP адресу
     * @param $IpAccesApi
     * @throws ForbiddenHttpException
     */
    private function CheckIpAccess($IpAccesApi)
    {
        $ip = Yii::$app->request->remoteIP;

        $CheckIP = new CheckIP($IpAccesApi);
        if (!$CheckIP->MatchIP($ip)) {
            throw new ForbiddenHttpException("Доступ запрещен");
        }
    }

    public function Req()
    {
        return $this->req;
    }

    public function GetReq($fld, $defval = null)
    {
        return isset($this->req[$fld]) ? $this->req[$fld] : $defval;
    }

    public function GetReqs($flds)
    {
        $ret = [];
        foreach ($flds as $fld) {
            $ret[$fld] = isset($this->req[$fld]) ? $this->req[$fld] : null;
        }
        return $ret;
    }

    /**
     * @param $IdPay
     * @return string
     */
    public function getLinkOutCard($IdPay)
    {
        if (Yii::$app->params['DEVMODE'] == 'Y') {
            return 'http://127.0.0.1:806/mfo/default/outcard/' . $IdPay;
        } elseif (Yii::$app->params['TESTMODE'] == 'Y') {
            return 'https://'.$_SERVER['SERVER_NAME'].'/mfo/default/outcard/' . $IdPay;
        } else {
            return 'https://api.vepay.online/mfo/default/outcard/' . $IdPay;
        }
    }
}