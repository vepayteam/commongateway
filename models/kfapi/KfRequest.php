<?php


namespace app\models\kfapi;

use app\models\bank\TCBank;
use app\models\payonline\Partner;
use Yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class KfRequest
{
    public $IdPartner = 0;
    /** @var $partner Partner|null */
    public $partner = null;
    public $req;

    /**
     * Проверка доступа к АПИ
     *
     * @param $header
     * @param $body
     * @param int $IsMfo
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function CheckAuth($header, $body, $IsMfo = 1)
    {
        $kfAuth = new KfAuth();
        $kfAuth->load([
            'login' => isset($header['X-Login']) ? $header['X-Login'] : '',
            'token' => isset($header['X-Token']) ? $header['X-Token'] : '',
            'jsonreq' => $body
        ], '');
        if (!$kfAuth->validate()) {
            throw new BadRequestHttpException($kfAuth->GetError());
        }

        if ($kfAuth->Check($IsMfo)) {

            $this->IdPartner = $kfAuth->IdPartner;
            $this->partner = $kfAuth->partner;
            try {
                $this->req = Json::decode($body);
                //$this->req = json_decode($jsonReq, false, 512, JSON_BIGINT_AS_STRING);
            } catch (\Exception $e) {
                throw new BadRequestHttpException($e);
            }

        } else {
            throw new UnauthorizedHttpException();
        }
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

}