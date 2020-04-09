<?php

namespace app\modules\mfo\controllers;

use app\models\bank\TcbGate;
use app\models\payonline\Partner;
use Yii;
use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\kfapi\KfBalance;
use app\models\mfo\MfoReq;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class AccountController extends Controller
{
    use CorsTrait;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $this->updateBehaviorsCors($behaviors);
        return $behaviors;
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        if ($this->checkBeforeAction()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $this->enableCsrfValidation = false;
            return parent::beforeAction($action);
        }
        return false;
    }

    protected function verbs()
    {
        return [
            'balance' => ['POST'],
            'statements' => ['POST'],
        ];
    }

    /**
     * Баланс счета
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionBalance()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        if (!$mfo->mfo) {
            return ['status' => 0, 'mesage' => ''];
        }

        $partner = Partner::findOne(['ID' => $mfo->mfo]);

        $kfBal = new KfBalance();
        $kfBal->load($mfo->Req(), '');
        if (empty($kfBal->account)) {
            $kfBal->setAttributes([
                'account' => $partner->SchetTcb
            ]);
        }
        if (!$kfBal->validate()) {
            return ['status' => 0, 'mesage' => $kfBal->GetError()];
        }

        $bal = $kfBal->GetBalance($partner);
        $state = [
            'status' => 1,
            'message' => '',
            'amount' => round($bal, 2)
        ];

        /*$TcbGate = new TcbGate($mfo->mfo, TCBank::$AFTGATE);
        $tcBank = new TCBank($TcbGate);
        $ret = $tcBank->getBalance();
        if ($ret && isset($ret['status']) && $ret['status'] == 1) {
            $state = [
                'status' => 1,
                'message' => '',
                'amount' => round($ret['amount'], 2)
            ];
        } else {
            $state = ['status' => 0, 'message' => $ret['message'], 'amount' => '0.00'];
        }*/
        return $state;
    }

}