<?php


namespace app\services\auth;

use yii;
use app\models\mfo\MfoReq;
use yii\filters\auth\AuthMethod;

/**
 * Class HttpHeaderAuthService
 * @package app\services\auth
 */
class HttpHeaderAuthService extends AuthMethod
{
    /**
     * @var array
     */
    public $_authData = [];

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $this->_authData['mfo'] = new MfoReq();
        $this->_authData['mfo']->LoadData(Yii::$app->request->getRawBody());
        return true;
    }

    /**
     * @param string $fld
     * @param null $defval
     * @return mixed
     */
    public function getReq(string $fld, $defval = null)
    {
        return $this->_authData['mfo']->GetReq($fld, $defval);
    }

    /**
     * @return MfoReq
     */
    public function getMfo(): MfoReq
    {
        return $this->_authData['mfo'];
    }

    /**
     * @return string
     */
    public static function class(): string
    {
        return get_called_class();
    }
}
