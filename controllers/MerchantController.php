<?php

namespace app\controllers;

use app\models\api\CorsTrait;
use app\models\api\Reguser;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfFormPay;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\payonline\Uslugatovar;
use app\models\Payschets;
use app\services\LanguageService;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\GateException;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\CreateFormEcomPartsStrategy;
use app\services\payment\payment_strategies\CreateFormJkhPartsStrategy;
use app\services\payment\payment_strategies\IPaymentStrategy;
use app\services\PaySchetService;
use Yii;
use yii\db\Exception;
use yii\mutex\FileMutex;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

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

    /**
     * {@inheritDoc}
     */
    public function actions()
    {
        return [
            /** @todo Проверить использование этого экшена и удалить. */
            'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        if (in_array(Yii::$app->controller->action->id, [
            'form-pay',
            'pay-parts',
            'pay',
            'state',
            'reverseorder',
        ])) {
            $this->updateBehaviorsCors($behaviors);
        }
        return $behaviors;
    }

    /**
     * @return array
     * @todo Не используется - удалить, либо задействовать.
     */
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
     * {@inheritDoc}
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'form-pay',
            'pay-parts',
            'pay',
            'state',
            'reverseorder',
        ])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Docs
     *
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = '@app/views/layouts/swaggerlayout';

        return $this->render('/site/apidoc', ['url' => '/merchant/swagger']);
    }

    /**
     * Renders the index view for the module
     *
     * @return Response
     */
    public function actionSwagger()
    {
        return Yii::$app->response->sendFile(
            Yii::$app->basePath . '/doc/merchant.yaml',
            '',
            ['inline' => true, 'mimeType' => 'application/yaml']
        );
    }

    /**
     * Платеж по АПИ
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws CreatePayException
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     * @todo Проверить корректность работы метода: выяснить почему здесь неиспользуемая переменная $TcbGate.
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
            $usl = $kfPay->GetUslugJkh($kf->IdPartner);
        } else {
            $usl = $kfPay->GetUslugEcom($kf->IdPartner);
        }
        if (!$usl) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }
        $id = "{$kf->IdPartner} sum={$kfPay->amount} extid={$kfPay->extid}";
        Yii::warning("/merchant/pay id={$id}", 'mfo');
        $user = null;
        $regcard = (bool)$kf->GetReq('regcard', 0);
        if ($regcard) {
            $reguser = new Reguser();
            $user = $reguser->findUser('0', $kf->IdPartner . '-' . time(), md5($kf->IdPartner . '-' . time()), $kf->IdPartner, false);
        }
        Yii::warning("/merchant/pay CreatePay id={$id}", 'merchant');
        $mutex = new FileMutex();
        if (!empty($kfPay->extid)) {
            // проверка на повторный запрос
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

        $uslugatovar = Uslugatovar::findOne(['ID' => $usl]);

        try {
            $bankAdapterBuilder = new BankAdapterBuilder();
            $bankAdapterBuilder->build($kf->partner, $uslugatovar);
        } catch (GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        $partnerBankGate = $bankAdapterBuilder->getPartnerBankGate();

        $params = $this->paySchetService->payToMfo(
            $user,
            [$kfPay->descript],
            $kfPay,
            $usl,
            $partnerBankGate->BankId,
            $kf->IdPartner,
            0,
            $regcard
        );

        /** @var LanguageService $languageService */
        $languageService = Yii::$app->get(LanguageService::class);
        $languageService->saveApiLanguage($params['IdPay'], $kfPay->language);

        if (!empty($kfPay->extid)) {
            $mutex->release('getPaySchetExt' . $kfPay->extid);
        }

        //PCI DSS
        return [
            'status' => 1,
            'id' => (int)$params['IdPay'],
            'url' => $kfPay->GetPayForm($params['IdPay']),
            'message' => '',
        ];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
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

        if ($result['status'] == 1) {
            $kfFormPay->createFormElements($result['id']);
            $result['url'] = $kfFormPay->GetPayForm($result['id']);
        }
        return $result;
    }

    /**
     * @return mixed
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionPayParts()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $kfRequest = new KfRequest();
        $kfRequest->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        /** @var IPaymentStrategy $paymentStrategy */
        $paymentStrategy =
            $kfRequest->GetReq('type', 0) == 1
                ? new CreateFormJkhPartsStrategy($kfRequest)
                : new CreateFormEcomPartsStrategy($kfRequest);
        return $paymentStrategy->exec();
    }

    /**
     * Статус оплаты
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws UnauthorizedHttpException
     */
    public function actionState()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $IdPay = $kf->GetReq('id');

        $tcBank = new TCBank();
        $confirmPayResult = $tcBank->confirmPay($IdPay, $kf->IdPartner);
        if ($confirmPayResult && isset($confirmPayResult['status']) && $confirmPayResult['IdPay'] != 0) {
            $card = null;
            if ($confirmPayResult['status'] == 1) {
                $kfCard = new KfCard();
                $kfCard->scenario = KfCard::SCENARIO_GET;
                $kfCard->load($kf->req, '');
                if ($kfCard->validate()) {
                    $cardUser = $kfCard->FindKardByPay($kf->IdPartner, 0);
                    if ($cardUser) {
                        $card = [
                            'num' => $cardUser->NameCard,
                            'exp' => (string)$cardUser->SrokKard,
                            'id' => $cardUser->ID,
                        ];
                    }
                }
            }

            // $paySchet точно не будет null, в методе $tcBank->confirmPay() идет проверка
            $paySchet = PaySchet::findOne(['ID' => $IdPay]);

            $state = [
                'status' => (int)$confirmPayResult['status'],
                'serviceType' => $paySchet->uslugatovar->type->Name,
                'message' => (string)$confirmPayResult['message'],
                'rc' => isset($confirmPayResult['rc']) ? (string)$confirmPayResult['rc'] : '',
                'info' => $confirmPayResult['info'],
                'card' => $card,
                'channel' => $paySchet->bank->ChannelName,
            ];
        } else {
            $state = ['status' => 0, 'message' => 'Счет не найден'];
        }
        return $state;
    }

    /**
     * Отмена оплаты
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionReverseorder(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $idPay = $kf->GetReq('id');

        $payschets = new Payschets();
        $ps = $payschets->getSchetData(Yii::$app->request->post('id'), '', $kf->IdPartner);
        if ($ps && $ps['Status'] == 1) {
            $tcbGate = new TcbGate($kf->IdPartner, null, $ps['IsCustom']);

            $tcBank = new TCBank($tcbGate);
            $reversOrderResult = $tcBank->reversOrder($idPay);

            if ($reversOrderResult['state'] == 1) {
                $payschets->SetReversPay($ps['ID']);
                return ['status' => 1, 'message' => 'Операция отменена'];
            } else {
                return ['status' => 0, 'message' => (string)$reversOrderResult['message']];
            }
        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

}
