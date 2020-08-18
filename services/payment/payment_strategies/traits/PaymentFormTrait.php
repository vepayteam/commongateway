<?php


namespace app\services\payment\payment_strategies\traits;


use app\models\api\Reguser;
use app\models\bank\TcbGate;
use app\models\kfapi\KfRequest;
use app\models\payonline\CreatePay;
use yii\web\User;

/**
 * Trait PaymentFormTrait
 * @package app\services\payment\payment_strategies\traits
 * @param KfRequest $request
 * @param User $user
 */
trait PaymentFormTrait
{

    /**
     * @return TcbGate
     */
    private function getTkbGate()
    {
        return new TcbGate($this->request->IdPartner, $this->gate);
    }

    private function createPay()
    {
        $user = $this->getUser();
        $pay = new CreatePay($user);
        return $pay;
    }

    private function getUser()
    {
        if(!$this->user) {
            if ($this->request->GetReq('regcard',0)) {
                $reguser = new Reguser();
                $this->user = $reguser->findUser('0', $this->request->IdPartner . '-' . time(), md5($this->request->IdPartner . '-' . time()), $this->request->IdPartner, false);
            }
        }

        return $this->user;
    }

}
