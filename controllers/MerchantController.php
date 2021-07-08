<?php

namespace app\controllers;

use app\models\api\CorsTrait;
use app\models\api\Reguser;
use app\models\bank\BankMerchant;
use app\models\bank\IBank;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfFormPay;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\IBankAdapter;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\MerchantPayForm;
use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\CreateFormEcomPartsStrategy;
use app\services\payment\payment_strategies\CreateFormJkhPartsStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use app\services\payment\payment_strategies\merchant\MerchantPayCreateStrategy;
use app\services\PaySchetService;
use Yii;
use yii\db\Exception;
use yii\helpers\Url;
use yii\mutex\FileMutex;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MerchantController extends Controller
{
    use CorsTrait;

    /**
     * @var PaySchetService
     */
    private $paySchetService;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->paySchetService = \Yii::$app->get(PaySchetService::class);
    }

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
        if (in_array(Yii::$app->controller->action->id, [
            'form-pay',
            'pay-parts',
            'pay',
            'state',
            'reverseorder'
        ])) {
            $this->updateBehaviorsCors($behaviors);
        }
        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'form-pay' => ['POST'],
            'pay-parts' => ['POST'],
            'pay' => ['POST'],
            'state' => ['POST'],
            'reverseorder' => ['POST'],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'form-pay',
            'pay-parts',
            'pay',
            'state',
            'reverseorder'
        ])) {
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
     * @throws CreatePayException
     */
    public function actionPay()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfPay = new KfPay();
        $kfPay->scenario = KfPay::SCENARIO_FORM;
        $kfPay->load($kf->req, '');
        if (!$kfPay->validate()) {
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        if ($kf->GetReq('type', 0) == 1) {
            $gate = TCBank::$JKHGATE;
            $usl = $kfPay->GetUslugJkh($kf->IdPartner);
        } else {
            $gate = TCBank::$ECOMGATE;
            $usl = $kfPay->GetUslugEcom($kf->IdPartner);
        }
        $TcbGate = new TcbGate($kf->IdPartner, $gate);
        if (!$usl) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }
        $id = $kf->IdPartner . " sum=".$kfPay->amount . " extid=".$kfPay->extid;
        Yii::warning("/merchant/pay id=$id", 'mfo');
        $user = null;
        if ($kf->GetReq('regcard',0)) {
            $reguser = new Reguser();
            $user = $reguser->findUser('0', $kf->IdPartner . '-' . time(), md5($kf->IdPartner . '-' . time()), $kf->IdPartner, false);
        }
        Yii::warning("/merchant/pay CreatePay id=$id", 'merchant');
        $mutex = new FileMutex();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfPay->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $paramsExist = $this->paySchetService->getPaySchetExt($kfPay->extid, $usl, $kf->IdPartner);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'id' => (int)$paramsExist['IdPay'], 'url' => $kfPay->GetPayForm($paramsExist['IdPay']), 'message' => ''];
                } else {
                    return ['status' => 0, 'id' => 0, 'url' => '', 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }
        Yii::warning("merchant/pay payToMfo id=$id", 'merchant');
        $params = $this->paySchetService->payToMfo($user, [$kfPay->descript], $kfPay, $usl, 2, $kf->IdPartner, 0);
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

    }

    public function actionFormPay()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfFormPay = new KfFormPay();
        $kfFormPay->scenario = KfFormPay::SCENARIO_FORM;
        $kfFormPay->load($kf->req, '');
        if (!$kfFormPay->validate()) {
            return ['status' => 0, 'message' => $kfFormPay->GetError()];
        }
        $result = $this->actionPay();

        if($result['status'] == 1) {
            $kfFormPay->createFormElements($result['id']);
            $result['url'] = $kfFormPay->GetPayForm($result['id']);
        }
        return $result;
    }

    public function actionPayParts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $kfRequest = new KfRequest();
        $kfRequest->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);
        /** @var IPaymentStrategy $paymentStrategy */
        $paymentStrategy = null;

        if ($kfRequest->GetReq('type', 0) == 1) {
            $paymentStrategy = new CreateFormJkhPartsStrategy($kfRequest);
        } else {
            $paymentStrategy = new CreateFormEcomPartsStrategy($kfRequest);
        }
        return $paymentStrategy->exec();
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
                        $card = ['num' => $cardUser->NameCard, 'exp' => (string)$cardUser->SrokKard, 'id' => $cardUser->ID];
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

}
