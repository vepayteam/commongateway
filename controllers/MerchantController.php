<?php

namespace app\controllers;

use app\models\api\CorsTrait;
use app\models\api\Reguser;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\payonline\CreatePay;
use app\models\Payschets;
use app\services\payment\payment_strategies\CreateFormEcomStrategy;
use app\services\payment\payment_strategies\CreateFormJkhStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use app\services\payment\PaymentService;
use Yii;
use yii\db\Exception;
use yii\mutex\FileMutex;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MerchantController extends Controller
{
    use CorsTrait;

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        if (in_array(Yii::$app->controller->action->id, ['pay', 'state', 'reverseorder'])) {
            $this->updateBehaviorsCors($behaviors);
        }
        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'pay' => ['POST'],
            'state' => ['POST'],
            'reverseorder' => ['POST']
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['pay', 'state', 'reverseorder'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Docs
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = '@app/views/layouts/swaggerlayout';
        return $this->render('/site/apidoc', ['url' => '/merchant/swagger']);
    }

    /**
     * Renders the index view for the module
     * @return Response
     */
    public function actionSwagger()
    {
        return Yii::$app->response->sendFile(Yii::$app->basePath . '/doc/merchant.yaml', '', ['inline' => true, 'mimeType' => 'application/yaml']);
    }

    /**
     * Платеж по АПИ
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionPay()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $kfRequest = new KfRequest();
        $kfRequest->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        /** @var IPaymentStrategy $paymentStrategy */
        $paymentStrategy = null;
        switch($kfRequest->GetReq('type', 0)) {
            case 1:
                $paymentStrategy = new CreateFormJkhStrategy($kfRequest);
                break;
            default:
                $paymentStrategy = new CreateFormEcomStrategy($kfRequest);
                break;
        }

        return $paymentStrategy->exec();
//
//        Yii::$app->response->format = Response::FORMAT_JSON;
//
//        $kf = new KfRequest();
//        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);
//
//        $kfPay = new KfPay();
//        $kfPay->scenario = KfPay::SCENARIO_FORM;
//        $kfPay->load($kf->req, '');
//        if (!$kfPay->validate()) {
//            return ['status' => 0, 'message' => $kfPay->GetError()];
//        }
//
//        if ($kf->GetReq('type', 0) == 1) {
//            $gate = TCBank::$JKHGATE;
//            $usl = $kfPay->GetUslugJkh($kf->IdPartner);
//        } else {
//            $gate = TCBank::$ECOMGATE;
//            $usl = $kfPay->GetUslugEcom($kf->IdPartner);
//        }
//        $TcbGate = new TcbGate($kf->IdPartner, $gate);
//        if (!$usl || !$TcbGate->IsGate()) {
//            return ['status' => 0, 'message' => 'Услуга не найдена'];
//        }
//
//        Yii::warning('/merchant/pay id='. $kf->IdPartner . " sum=".$kfPay->amount . " extid=".$kfPay->extid, 'mfo');
//
//        $user = null;
//        if ($kf->GetReq('regcard',0)) {
//            $reguser = new Reguser();
//            $user = $reguser->findUser('0', $kf->IdPartner . '-' . time(), md5($kf->IdPartner . '-' . time()), $kf->IdPartner, false);
//        }
//
//        $pay = new CreatePay($user);
//        $mutex = new FileMutex();
//        if (!empty($kfPay->extid)) {
//            //проверка на повторный запрос
//            if (!$mutex->acquire('getPaySchetExt' . $kfPay->extid, 30)) {
//                throw new Exception('getPaySchetExt: error lock!');
//            }
//            $paramsExist = $pay->getPaySchetExt($kfPay->extid, $usl, $kf->IdPartner);
//            if ($paramsExist) {
//                if ($kfPay->amount == $paramsExist['sumin']) {
//                    return ['status' => 1, 'id' => (int)$paramsExist['IdPay'], 'url' => $kfPay->GetPayForm($paramsExist['IdPay']), 'message' => ''];
//                } else {
//                    return ['status' => 0, 'id' => 0, 'url' => '', 'message' => 'Нарушение уникальности запроса'];
//                }
//            }
//        }
//
//        $params = $pay->payToMfo($user, [$kfPay->descript], $kfPay, $usl, TCBank::$bank, $kf->IdPartner, 0);
//        if (!empty($kfPay->extid)) {
//            $mutex->release('getPaySchetExt' . $kfPay->extid);
//        }
//
//        //PCI DSS
//        return [
//            'status' => 1,
//            'id' => (int)$params['IdPay'],
//            'url' => $kfPay->GetPayForm($params['IdPay']),
//            'message' => ''
//        ];
    }

    /**
     * Статус оплаты
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionState()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
	
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(),0);

        $IdPay = $kf->GetReq('id');

        $tcBank = new TCBank();
        $ret = $tcBank->confirmPay($IdPay, $kf->IdPartner);
        if ($ret && isset($ret['status']) && $ret['IdPay'] != 0) {
            $card = null;
            if ($ret['status'] == 1) {
                $kfCard = new KfCard();
                $kfCard->scenario = KfCard::SCENARIO_GET;
                $kfCard->load($kf->req, '');
                if ($kfCard->validate()) {
                    $cardUser = $kfCard->FindKardByPay($kf->IdPartner, 0);
                    if ($cardUser) {
                        $card = ['num' => $cardUser->NameCard, 'exp' => $cardUser->SrokKard, 'id' => $cardUser->ID];
                    }
                }
            }
            $state = ['status' => (int)$ret['status'], 'message' => (string)$ret['message'], 'rc' => isset($ret['rc']) ?(string)$ret['rc'] : '', 'info' => $ret['info'], 'card' => $card];
        } else {
            $state = ['status' => 0, 'message' => 'Счет не найден'];
        }
        return $state;
    }

    /**
     * Отмена оплаты
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionReverseorder()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(),0);

        $IdPay = $kf->GetReq('id');

        $payschets = new Payschets();
        $ps = $payschets->getSchetData(Yii::$app->request->post('id'), '', $kf->IdPartner);
        if ($ps && $ps['Status'] == 1) {

            $TcbGate = new TcbGate($kf->IdPartner,null, $ps['IsCustom']);

            $tcBank = new TCBank($TcbGate);
            $res = $tcBank->reversOrder($IdPay);

            if ($res['state'] == 1) {
                $payschets->SetReversPay($ps['ID']);
                return ['status' => 1, 'message' => 'Операция отменена'];
            } else {
                return ['status' => 0, 'message' => (string)$res['message']];
            }

        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];

    }

    /**
     * @return PaymentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getPaymentService()
    {
        return Yii::$container->get('PaymentService');
    }

}
