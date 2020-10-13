<?php

namespace app\modules\mfo\controllers;

use app\models\api\CorsTrait;
use app\models\bank\BankMerchant;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\CardToken;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfFormPay;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfPayParts;
use app\models\mfo\MfoReq;
use app\models\payonline\CreatePay;
use app\models\PayschetPart;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\MfoLkPayForm;
use app\services\payment\payment_strategies\CreateFormMfoAftPartsStrategy;
use app\services\payment\payment_strategies\CreateFormMfoEcomPartsStrategy;
use app\services\payment\payment_strategies\IMfoStrategy;
use app\services\payment\payment_strategies\mfo\MfoPayLkCreateStrategy;
use Yii;
use yii\base\Exception;
use yii\helpers\VarDumper;
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
            'lk' => ['POST'],
            'form-lk' => ['POST'],
            'lk-parts' => ['POST'],
            'auto' => ['POST'],
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
    public function actionLk()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $form = new MfoLkPayForm();
        $form->partner = $mfo->getPartner();
        if (!$form->load($mfo->Req(), '') || !$form->validate()) {
            Yii::warning("pay/lk: " . $form->GetError());
            return ['status' => 0, 'message' => $form->getError()];
        }

        Yii::warning('/pay/lk mfo='. $mfo->mfo . " sum=" . $form->amount . " extid=" . $form->extid, 'mfo');
        $paymentStrategy = new MfoPayLkCreateStrategy($form);
        try {
            $payschet = $paymentStrategy->exec();
        } catch (CreatePayException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        $urlForm = Yii::$app->params['domain'] . '/pay/form/' . $payschet->ID;
        return [
            'status' => 1,
            'id' => (int)$payschet->ID,
            'url' => $urlForm,
            'message' => ''
        ];
    }

    public function actionFormLk()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $kfFormPay = new KfFormPay();
        $kfFormPay->scenario = KfFormPay::SCENARIO_FORM;
        $kfFormPay->load($mfo->Req(), '');
        if (!$kfFormPay->validate()) {
            return ['status' => 0, 'message' => $kfFormPay->GetError()];
        }
        $result = $this->actionLk();

        if($result['status'] == 1) {
            $kfFormPay->createFormElements($result['id']);
            $result['url'] = $kfFormPay->GetPayForm($result['id']);
        }
        return $result;
    }

    public function actionLkParts()
    {
        $mfoReq = new MfoReq();
        $mfoReq->LoadData(Yii::$app->request->getRawBody());

        // TODO: refact
        $kfPay = new KfPayParts();
        $kfPay->scenario = KfPayParts::SCENARIO_FORM;
        $kfPay->load($mfoReq->Req(), '');
        if (!$kfPay->validate()) {
            Yii::warning("pay/lk: " . $kfPay->GetError());
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        Yii::warning('/pay/lk mfo=' . $mfoReq->mfo . " sum=" . $kfPay->amount . " extid=" . $kfPay->extid, 'mfo');

        $gate = $kfPay->IsAftGate($mfoReq->mfo) ? TCBank::$AFTGATE : TCBank::$ECOMGATE;

        /** @var IMfoStrategy $mfoStrategy */
        $mfoStrategy = null;
        if ($kfPay->IsAftGate($mfoReq->mfo)) {
            $mfoStrategy = new CreateFormMfoAftPartsStrategy($mfoReq);
        } else {
            $mfoStrategy = new CreateFormMfoEcomPartsStrategy($mfoReq);
        }
        return $mfoStrategy->exec();
    }

        /**
     * Автопогашение займа
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionAuto()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($mfo->Req(), '');
        if (!$kfCard->validate()) {
            Yii::warning("pay/auto: не указана карта");
            return ['status' => 0, 'message' => 'Не указана карта'];

        }
        $Card = $kfCard->FindKard($mfo->mfo,0);
        if (!$Card) {
            Yii::warning("pay/auto: нет такой карты");
            return ['status' => 0, 'message' => 'Нет такой карты'];
        }

        $kfPay = new KfPay();
        $kfPay->scenario = KfPay::SCENARIO_AUTO;
        $kfPay->load($mfo->Req(), '');
        if (!$kfPay->validate()) {
            Yii::warning("pay/auto: ".$kfPay->GetError());
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        $TcbGate = new TcbGate($mfo->mfo, TCBank::$AUTOPAYGATE);
        $usl = $kfPay->GetUslugAuto($mfo->mfo);

        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Нет шлюза'];
        }

        Yii::warning('/pay/auto mfo='. $mfo->mfo . " sum=".$kfPay->amount . " extid=".$kfPay->extid, 'mfo');

        $pay = new CreatePay();
        $mutex = new FileMutex();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfPay->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $paramsExist = $pay->getPaySchetExt($kfPay->extid, $usl, $mfo->mfo);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'message' => '', 'id' => (int)$paramsExist['IdPay']];
                } else {
                    Yii::warning("pay/auto: Нарушение уникальности запроса");
                    return ['status' => 0, 'message' => 'Нарушение уникальности запроса', 'id' => 0];
                }
            }
        }

        //деление на 7 шлюзов (3 запроса по одной карте в сутки)
        $TcbGate->AutoPayIdGate = $kfPay->GetAutopayGate();
        if (!$TcbGate->AutoPayIdGate) {
            Yii::warning("pay/auto: нет больше шлюзов");
            return ['status' => 0, 'message' => 'нет больше шлюзов'];
        }
        if ($Card && $Card->IdPan > 0) {
            $CardToken = new CardToken();
            $cardnum = $CardToken->GetCardByToken($Card->IdPan);
        }
        if (empty($cardnum)) {
            Yii::warning("pay/auto: empty card", 'mfo');
            return ['status' => 0, 'message' => 'empty card'];
        }

        $kfPay->timeout = 30;
        $params = $pay->payToMfo($kfCard->user, [$kfPay->extid, $Card->ID, $TcbGate->AutoPayIdGate], $kfPay, $usl, TCBank::$bank, $mfo->mfo, $TcbGate->AutoPayIdGate);
        if (!empty($kfPay->extid)) {
            $mutex->release('getPaySchetExt' . $kfPay->extid);
        }
        //$params['CardFrom'] = $Card->ExtCardIDP;
        $params['card']['number'] = $cardnum;
        $params['card']['holder'] = $Card->CardHolder;
        $params['card']['year'] =  $Card->getYear();
        $params['card']['month'] = $Card->getMonth();

        $payschets = new Payschets();
        $pay->setKardToPaySchet($params['IdPay'], $Card->ID);

        //данные карты
        $payschets->SetCardPay($params['IdPay'], [
            'number' => $Card->CardNumber,
            'holder' => $Card->CardHolder,
            'year' => $Card->getYear(),
            'month' => $Card->getMonth()
        ]);

        $tcBank = new TCBank($TcbGate);
        //$ret = $tcBank->createAutoPay($params);
        $ret = $tcBank->createRecurrentPay($params);

        if ($ret['status'] == 1) {
            //сохранение номера транзакции
            $payschets->SetBankTransact([
                'idpay' => $params['IdPay'],
                'trx_id' => $ret['transac'],
                'url' => ''
            ]);

        } else {
            $pay->CancelReq($params['IdPay'],'Платеж не проведен');
        }

        return ['status' => 1, 'message' => '', 'id' => (int)$params['IdPay']];
    }

    // TODO: refact to strategies
    public function actionAutoParts()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($mfo->Req(), '');
        if (!$kfCard->validate()) {
            Yii::warning("pay/auto: не указана карта");
            return ['status' => 0, 'message' => 'Не указана карта'];

        }
        $Card = $kfCard->FindKard($mfo->mfo,0);
        if (!$Card) {
            Yii::warning("pay/auto: нет такой карты");
            return ['status' => 0, 'message' => 'Нет такой карты'];
        }

        $kfPay = new KfPayParts();
        $kfPay->scenario = KfPayParts::SCENARIO_AUTO;
        $kfPay->load($mfo->Req(), '');
        if (!$kfPay->validate()) {
            Yii::warning("pay/auto: ".$kfPay->GetError());
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        $TcbGate = new TcbGate($mfo->mfo, TCBank::$PARTSGATE);
        $usl = $kfPay->GetUslugAuto($mfo->mfo);

        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Нет шлюза'];
        }

        Yii::warning('/pay/auto mfo='. $mfo->mfo . " sum=".$kfPay->amount . " extid=".$kfPay->extid, 'mfo');

        $pay = new CreatePay();
        $mutex = new FileMutex();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfPay->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $paramsExist = $pay->getPaySchetExt($kfPay->extid, $usl, $mfo->mfo);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'message' => '', 'id' => (int)$paramsExist['IdPay']];
                } else {
                    Yii::warning("pay/auto: Нарушение уникальности запроса");
                    return ['status' => 0, 'message' => 'Нарушение уникальности запроса', 'id' => 0];
                }
            }
        }

        //деление на 7 шлюзов (3 запроса по одной карте в сутки)
        $TcbGate->AutoPayIdGate = $kfPay->GetAutopayGate();
        if (!$TcbGate->AutoPayIdGate) {
            Yii::warning("pay/auto: нет больше шлюзов");
            return ['status' => 0, 'message' => 'нет больше шлюзов'];
        }
        if ($Card && $Card->IdPan > 0) {
            $CardToken = new CardToken();
            $cardnum = $CardToken->GetCardByToken($Card->IdPan);
        }
        if (empty($cardnum)) {
            Yii::warning("pay/auto: empty card", 'mfo');
            return ['status' => 0, 'message' => 'empty card'];
        }

        $kfPay->timeout = 30;
        $params = $pay->payToMfo($kfCard->user, [$kfPay->extid, $Card->ID, $TcbGate->AutoPayIdGate], $kfPay, $usl, TCBank::$bank, $mfo->mfo, $TcbGate->AutoPayIdGate);

        foreach ($mfo->Req()['parts'] as $part) {
            $payschetPart = new PayschetPart();
            $payschetPart->PayschetId = $params['IdPay'];
            $payschetPart->PartnerId = $part['merchant_id'];
            $payschetPart->Amount = $part['amount'] * 100;
            $payschetPart->save();
        }

        if (!empty($kfPay->extid)) {
            $mutex->release('getPaySchetExt' . $kfPay->extid);
        }
        //$params['CardFrom'] = $Card->ExtCardIDP;
        $params['card']['number'] = $cardnum;
        $params['card']['holder'] = $Card->CardHolder;
        $params['card']['year'] =  $Card->getYear();
        $params['card']['month'] = $Card->getMonth();

        $payschets = new Payschets();
        $pay->setKardToPaySchet($params['IdPay'], $Card->ID);

        //данные карты
        $payschets->SetCardPay($params['IdPay'], [
            'number' => $Card->CardNumber,
            'holder' => $Card->CardHolder,
            'year' => $Card->getYear(),
            'month' => $Card->getMonth()
        ]);

        $tcBank = new TCBank($TcbGate);
        //$ret = $tcBank->createAutoPay($params);
        $ret = $tcBank->createRecurrentPay($params);

        if ($ret['status'] == 1) {
            //сохранение номера транзакции
            $payschets->SetBankTransact([
                'idpay' => $params['IdPay'],
                'trx_id' => $ret['transac'],
                'url' => ''
            ]);

        } else {
            $pay->CancelReq($params['IdPay'],'Платеж не проведен');
        }

        return ['status' => 1, 'message' => '', 'id' => (int)$params['IdPay']];
    }

    /**
     * Статус платежа погашения
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionState()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $IdPay = $mfo->GetReq('id');

        $payschets = new Payschets();
        $params = $payschets->getSchetData($IdPay,null, $mfo->mfo);
        if ($params) {
            $merchBank = BankMerchant::Create($params);
            $ret = $merchBank->confirmPay($IdPay, $mfo->mfo);
            if ($ret && isset($ret['status']) && $ret['IdPay'] != 0) {
                return ['status' => (int)$ret['status'], 'message' => (string)$ret['message'], 'rc' => isset($ret['rc']) ?(string)$ret['rc'] : ''];
            }
        }
        return ['status' => 0, 'message' => 'Счет не найден'];
    }

}
