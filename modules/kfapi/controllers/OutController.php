<?php

namespace app\modules\kfapi\controllers;

use app\models\antifraud\AntiFraudRefund;
use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\CardToken;
use app\models\kfapi\KfOut;
use app\models\kfapi\KfRequest;
use app\models\mfo\MfoTestError;
use app\models\payonline\Cards;
use app\models\Payschets;
use app\services\payment\forms\OutPayAccountForm;
use app\services\payment\payment_strategies\mfo\MfoOutPayAccountStrategy;
use app\services\PaySchetService;
use Yii;
use yii\base\Exception;
use yii\helpers\VarDumper;
use yii\mutex\FileMutex;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class OutController extends Controller
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

    /**
     * {@inheritDoc}
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);

        try {
            Yii::info([
                'endpoint' => $action->uniqueId,
                'header' => Yii::$app->request->headers->toArray(),
                'body' => Yii::$app->request->post(),
                'return' => (array) $result,
            ], 'mfo_' . $action->controller->id . '_' . $action->id);
        } catch (\Exception $e) {
            Yii::error([$e->getMessage(), $e->getTrace(), $e->getFile(), $e->getLine()], 'kfapi_out');
        }

        return $result;
    }

    protected function verbs()
    {
        return [
            'paycard' => ['POST'],
            'ul' => ['POST'],
            'fl' => ['POST'],
            'int' => ['POST'],
            'state' => ['POST'],
        ];
    }

    /**
     * Выплата займа на карту
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionPaycard()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());
        $kfOut = new KfOut();
        $kfOut->scenario = KfOut::SCENARIO_CARD;
        $kfOut->load($kf->req, '');
        if (!$kfOut->validate()) {
            return ['status' => 0, 'message' => $kfOut->GetError()];
        }

        $TcbGate = new TcbGate($kf->IdPartner, TCBank::$OCTGATE);
        $usl = $kfOut->GetUslug($kf->IdPartner);
        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }

        $mutex = new FileMutex();
        if (!empty($kfOut->extid)) {
            if (!$mutex->acquire('getPaySchetExt' . $kfOut->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }

            //проверка на повторный запрос
            $params = $this->paySchetService->getPaySchetExt($kfOut->extid, $usl, $kf->IdPartner);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
                } else {
                    return ['status' => 0, 'id' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        Yii::warning('/out/paycard kfmfo='. $kf->IdPartner . " sum=".$kfOut->amount . " extid=".$kfOut->extid, 'mfo');

        $cartToken = new CardToken();
        if (($token = $cartToken->CheckExistToken($kfOut->cardnum, 0)) == 0) {
            $token = $cartToken->CreateToken($kfOut->cardnum, 0, '');
        }
        if ($token === 0){
            return ['status' => 0, 'message' => 'Ошибка формирования платежа'];
        }
        //здесь происходит сохранение платежа в бд.
        $params = $this->paySchetService->payToCard(null, [Cards::MaskCard($kfOut->cardnum), $token, $kfOut->document_id, $kfOut->fullname], $kfOut, $usl, TCBank::$bank, $kf->IdPartner, $kfOut->sms);
        if (!empty($kfOut->extid)) {
            $mutex->release('getPaySchetExt' . $kfOut->extid);
        }

        $params['CardNum'] = $kfOut->cardnum;

        $payschets = new Payschets();
        //данные карты
        $payschets->SetCardPay($params['IdPay'], [
            'number' => $kfOut->cardnum,
            'holder' => '',
            'month' => 0,
            'year' => 0
        ]);

        //антифрод должен рабоатть после записи в базу.
        $anti_fraud = new AntiFraudRefund($params['IdPay'], $kf->IdPartner, Cards::MaskCard($kfOut->cardnum));
        if (!$anti_fraud->validate()) {
            $this->paySchetService->cancelReq($params['IdPay'], 'Повторный платеж');
            Yii::warning("out/paycard: Повторный платеж", 'mfo');
            return ['status' => 0, 'id' => 0, 'message' => 'Повторный платеж'];
        }

        /*if (Yii::$app->params['TESTMODE'] == 'Y' && $kfOut->sms === 0) {
            //заглушка - тест выплаты на карту
            $test = new MfoTestError();
            if (!$test->TestCancelCards($kfOut->cardnum, $params['IdPay'])) {
                $test->ConfirmOut($kfOut->cardnum, $params['IdPay']);
            }
            return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
        }*/

        if($kfOut->sms === 0) {
            $tcBank = new TCBank($TcbGate);
            $ret = $tcBank->transferToCard($params);
            if ($ret && $ret['status'] == 1) {
                //сохранение номера транзакции
                $payschets->SetBankTransact([
                    'idpay' => $params['IdPay'],
                    'trx_id' => $ret['transac'],
                    'url' => ''
                ]);

            } else {
                $this->paySchetService->cancelReq($params['IdPay'],'Платеж не проведен');
            }
        }

        return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
    }

    /**
     * Вывод на счет юрлица
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionUl()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $outPayAccountForm = new OutPayAccountForm();
        $outPayAccountForm->load($kf->req, '');
        $outPayAccountForm->partner = $kf->partner;
        if (!$outPayAccountForm->validate()) {
            return ['status' => 0, 'message' => $outPayAccountForm->GetError()];
        }

        $mfoOutPayAccountStrategy = new MfoOutPayAccountStrategy($outPayAccountForm);

        try {
            /** @var PaySchet $paySchet */
            $paySchet = $mfoOutPayAccountStrategy->exec();
            return [
                'status' => 1,
                'id' => $paySchet->ID,
                'message' => '',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Вывод на счет физлица
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionFl()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $kfOut = new KfOut();
        $kfOut->scenario = KfOut::SCENARIO_FL;
        $kfOut->load($kf->req, '');
        if (!$kfOut->validate()) {
            return ['status' => 0, 'message' => $kfOut->GetError()];
        }

        $TcbGate = new TcbGate($kf->IdPartner, TCBank::$SCHETGATE);
        $usl = $kfOut->GetUslug($kf->IdPartner);
        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }

        $kfOut->descript = str_replace(" ", " ", $kfOut->descript); //0xA0 пробел на 0x20

        $mutex = new FileMutex();
        if (!empty($kfOut->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfOut->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $params = $this->paySchetService->getPaySchetExt($kfOut->extid, $usl, $kf->IdPartner);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
                } else {
                    return ['status' => 0, 'id' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        Yii::warning('/out/fl kfmfo='. $kf->IdPartner . " sum=".$kfOut->amount . " extid=".$kfOut->extid, 'mfo');

        $params = $this->paySchetService->payToCard(
            null,
            [$kfOut->account, $kfOut->bic, $kfOut->fio, $kfOut->descript],
            $kfOut,
            $usl,
            TCBank::$bank,
            $kf->IdPartner,
            $kfOut->sms
        );
        if (!empty($kfOut->extid)) {
            $mutex->release('getPaySchetExt' . $kfOut->extid);
        }
        $params['name'] = $kfOut->fio;
        $params['bic'] = $kfOut->bic;
        $params['account'] = $kfOut->account;
        $params['descript'] = $kfOut->descript;

        /*if (Yii::$app->params['TESTMODE'] == 'Y' && $kfOut->sms === 0) {
            //заглушка - тест выплаты на счет
            $test = new MfoTestError();
            if (!$test->TestCancelSchet($kfOut->account, $params['IdPay'])) {
                $test->ConfirmOut($params['IdPay']);
            }
            return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
        }*/

        if ($kfOut->sms === 0) {
            $tcBank = new TCBank($TcbGate);
            $ret = $tcBank->transferToAccount($params);
            if ($ret && $ret['status'] == 1) {
                //сохранение номера транзакции
                $payschets = new Payschets();
                $payschets->SetBankTransact([
                    'idpay' => $params['IdPay'],
                    'trx_id' => $ret['transac'],
                    'url' => ''
                ]);

            } else {
                $this->paySchetService->cancelReq($params['IdPay'],'Платеж не проведен');
            }

        }

        return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
    }

    /**
     * Вывод НДФЛ
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionNdfl()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());
        $kfOut = new KfOut();
        $kfOut->scenario = KfOut::SCENARIO_NDFL;
        $kfOut->load($kf->req, '');
        if (!$kfOut->validate()) {
            return ['status' => 0, 'message' => $kfOut->GetError()];
        }

        $TcbGate = new TcbGate($kf->IdPartner, TCBank::$SCHETGATE);
        $usl = $kfOut->GetUslug($kf->IdPartner);
        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }

        $kfOut->descript = str_replace(" ", " ", $kfOut->descript); //0xA0 пробел на 0x20

        $mutex = new FileMutex();
        if (!empty($kfOut->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfOut->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $params = $this->paySchetService->getPaySchetExt($kfOut->extid, $usl, $kf->IdPartner);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
                } else {
                    return ['status' => 0, 'id' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        Yii::warning('/out/ndfl kfmfo='. $kf->IdPartner . " sum=".$kfOut->amount . " extid=".$kfOut->extid, 'mfo');

        $params = $this->paySchetService->payToCard(
            null,
            [$kfOut->account, $kfOut->bic, $kfOut->name, $kfOut->inn, $kfOut->kpp, $kfOut->descript],
            $kfOut,
            $usl,
            TCBank::$bank,
            $kf->IdPartner,
            $kfOut->sms
        );
        if (!empty($kfOut->extid)) {
            $mutex->release('getPaySchetExt' . $kfOut->extid);
        }

        if ($kfOut->sms === 0) {
            $tcBank = new TCBank($TcbGate);
            $ret = $tcBank->transferToNdfl($kfOut->GetNdflJson($params, $kf->partner));
            if ($ret && $ret['status'] == 1) {
                //сохранение номера транзакции
                $payschets = new Payschets();
                $payschets->SetBankTransact([
                    'idpay' => $params['IdPay'],
                    'trx_id' => $ret['transac'],
                    'url' => ''
                ]);
                //статус отдельно не надо получать
                $payschets->confirmPay([
                    'idpay' => $params['IdPay'],
                    'result_code' => 1,
                    'trx_id' => $ret['transac'],
                    'ApprovalCode' => '',
                    'RRN' => $ret['rrn'],
                    'message' => ''
                ]);

            } else {
                $this->paySchetService->cancelReq($params['IdPay'], $ret['message']);
            }
        }

        return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
    }

    /**
     * Вывод на счет юрлица внутри ТКБ
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionInt()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $kfOut = new KfOut();
        $kfOut->scenario = KfOut::SCENARIO_INT;
        $kfOut->load($kf->req, '');
        if (!$kfOut->validate()) {
            return ['status' => 0, 'message' => $kfOut->GetError()];
        }

        $TcbGate = new TcbGate($kf->IdPartner, TCBank::$SCHETGATE);
        $usl = $kfOut->GetUslug($kf->IdPartner);
        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }

        $kfOut->descript = str_replace(" ", " ", $kfOut->descript); //0xA0 пробел на 0x20

        $mutex = new FileMutex();
        if (!empty($kfOut->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfOut->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $params = $this->paySchetService->getPaySchetExt($kfOut->extid, $usl, $kf->IdPartner);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
                } else {
                    return ['status' => 0, 'id' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        $kfOut->bic = TCBank::BIC;
        $params = $this->paySchetService->payToCard(
            null,
            [$kfOut->account, $kfOut->bic, $kfOut->name, $kfOut->inn, $kfOut->kpp, $kfOut->descript],
            $kfOut,
            $usl,
            TCBank::$bank,
            $kf->IdPartner
        );
        if (!empty($kfOut->extid)) {
            $mutex->release('getPaySchetExt' . $kfOut->extid);
        }
        $params['name'] = $kfOut->name;
        $params['inn'] = trim($kfOut->inn);
        $params['kpp'] = $kfOut->kpp;
        $params['bic'] = $kfOut->bic;
        $params['account'] = $kfOut->account;
        $params['descript'] = $kfOut->descript;

        $tcBank = new TCBank($TcbGate);
        $ret = $tcBank->transferToAccount($params);
        if ($ret && $ret['status'] == 1) {
            //сохранение номера транзакции
            $payschets = new Payschets();
            $payschets->SetBankTransact([
                'idpay' => $params['IdPay'],
                'trx_id' => $ret['transac'],
                'url' => ''
            ]);

        } else {
            $this->paySchetService->cancelReq($params['IdPay'],'Платеж не проведен');
        }

        return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
    }

    /**
     * Статус выплаты
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

        $kfOut = new KfOut();
        $kfOut->scenario = KfOut::SCENARIO_STATE;
        $kfOut->load($kf->req, '');
        if (!$kfOut->validate()) {
            return ['status' => 0, 'message' => $kfOut->GetError()];
        }

        $tcBank = new TCBank();
        $ret = $tcBank->confirmPay($kfOut->id, $kf->IdPartner);
        if ($ret && isset($ret['status'])) {
            $state = ['status' => (int)$ret['status'], 'message' => (string)$ret['message']];
        } else {
            $state = ['status' => 0, 'message' => 'Счет не найден'];
        }
        return $state;
    }

}