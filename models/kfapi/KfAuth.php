<?php


namespace app\models\kfapi;

use app\models\payonline\Partner;
use Yii;
use yii\base\Model;
use yii\helpers\IpHelper;
use yii\web\ForbiddenHttpException;

class KfAuth extends Model
{
    public $IdPartner = 0;
    /** @var $partner Partner|null */
    public $partner = null;

    public $login;
    public $token;
    public $jsonreq;

    public function rules()
    {
        return [
            [['login', 'token'], 'string', 'max' => 100],
            [['jsonreq'], 'string', 'max' => 10000],
            [['login', 'token'], 'required', 'message' => 'Нет данных авторизации'],
            [['login', 'token', 'jsonreq'], 'required', 'message' => 'Нет JSON данных']
        ];
    }

    /**
     * @param $IsMfo
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function Check($IsMfo)
    {
        return $this->checkMfoToken($this->jsonreq, $this->login, $this->token, $IsMfo);
    }

    /**
     * @param $jsonReq
     * @param $login
     * @param $token
     * @param $IsMfo
     * @return bool
     * @throws ForbiddenHttpException
     */
    private function checkMfoToken($jsonReq, $login, $token, $IsMfo)
    {
        $partner = Partner::findOne(['IsDeleted' => 0, 'IsBlocked' => 0, 'IsMfo' => $IsMfo, 'ID' => $login]);
        if (!$partner){
            return false;
        }
        if (!empty($partner->IpAccesApi)) {
            $this->CheckIpAccess($partner->IpAccesApi);
        }
        if (Yii::$app->params['DEVMODE'] == 'Y' || sha1(sha1($partner->PaaswordApi) . sha1($jsonReq)) == $token) {
            $this->IdPartner = $partner->ID;
            $this->partner = $partner;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверка доступа по IP адресу
     *
     * @param $IpAccesApi - адреса через запятую, пример 127.0.0.1,192.168.1.0/24
     * @return bool|void
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

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

}