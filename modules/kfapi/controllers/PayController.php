<?php

namespace app\modules\kfapi\controllers;

use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\payonline\CreatePay;
use app\models\Payschets;
use app\models\TU;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class PayController extends Controller
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
            'in' => ['POST'],
            'state' => ['POST'],
        ];
    }

    /**
     * Погашение займа из ЛК МФО
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionIn()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $kfPay = new KfPay();
        $kfPay->scenario = KfPay::SCENARIO_FORM;
        $kfPay->load($kf->req, '');
        if (!$kfPay->validate()) {
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        $typeUsl = $kfPay->IsAftGate($kf->IdPartner) ? TU::$POGASHATF : TU::$POGASHECOM;
        $TcbGate = new TcbGate($kf->IdPartner, null, $typeUsl);
        $usl = $kfPay->GetUslug($kf->IdPartner, $typeUsl);
        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }

        Yii::warning('/pay/in kfmfo='. $kf->IdPartner . " sum=".$kfPay->amount . " extid=".$kfPay->extid, 'mfo');

        $pay = new CreatePay();
        $mutex = new FileMutex();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfPay->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $paramsExist = $pay->getPaySchetExt($kfPay->extid, $usl, $kf->IdPartner);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'id' => (int)$paramsExist['IdPay'], 'url' => $kfPay->GetPayForm($paramsExist['IdPay']), 'message' => ''];
                } else {
                    return ['status' => 0, 'id' => 0, 'url' => '', 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        $params = $pay->payToMfo(null, [$kfPay->document_id, $kfPay->fullname], $kfPay, $usl, TCBank::$bank, $kf->IdPartner, 0);
        if (!empty($kfPay->extid)) {
            $mutex->release('getPaySchetExt' . $kfPay->extid);
        }

        //PCI DSS
        return [
            'status' => 1,
            'id' => (int)$params['IdPay'],
            'url' => $kfPay->GetPayForm($params['IdPay']),
            'message' => ''
        ];

        /*
        $tcBank = new TCBank($gate, $kf->getBackUrl(), 1, $kf->GetGates());
        $ret = $tcBank->formPayOnly($params);

        if ($ret['status'] == 1) {
            //сохранение номера транзакции
            $payschets = new Payschets();
            $payschets->SetBankTransact([
                'idpay' => $params['IdPay'],
                'trx_id' => $ret['transac'],
                'url' => $ret['url']
            ]);

        } else {
            $pay->CancelReq($params['IdPay']);
        }

        return [
            'status' => 1,
            'id' => $params['IdPay'],
            'url' => $kfPay->getLinkForm($params['IdPay']),
            'message' => ''
        ];
        */

    }

    /**
     * Статус платежа
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws ForbiddenHttpException
     */
    public function actionState()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $kfPay = new KfPay();
        $kfPay->scenario = KfPay::SCENARIO_STATE;
        $kfPay->load($kf->req, '');
        if (!$kfPay->validate()) {
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        $tcBank = new TCBank();
        $ret = $tcBank->confirmPay($kfPay->id, $kf->IdPartner);
        if ($ret && isset($ret['status']) && $ret['IdPay'] != 0) {
            $state = ['status' => (int)$ret['status'], 'message' => (string)$ret['message']];
        } else {
            $state = ['status' => 0, 'message' => 'Счет не найден'];
        }
        return $state;
    }

}
