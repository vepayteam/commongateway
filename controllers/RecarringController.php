<?php

namespace app\controllers;

use app\models\api\CorsTrait;
use app\models\api\Reguser;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\CardToken;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\payonline\CreatePay;
use app\models\Payschets;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class RecarringController extends Controller
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
            'info' => ['POST'],
            'reg' => ['POST'],
            'get' => ['POST'],
            'del' => ['POST'],
            'pay' => ['POST'],
            'state' => ['POST']
        ];
    }

    /**
     * Получить данные карты
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws ForbiddenHttpException
     */
    public function actionInfo()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($kf->req, '');
        if (!$kfCard->validate()) {
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }

        $card = $kfCard->FindKard($kf->IdPartner, 0);

        //информация и карте
        if ($card) {
            return [
                'status' => 1,
                'card' => [
                    'id' => (int)($card->ID),
                    'num' => (string)($card->CardNumber),
                    'exp' => $card->getMonth() . "/" . $card->getYear()
                ]
            ];
        }
        return ['status' => 0, 'message' => ''];
    }

    /**
     * Зарегистрировать карту
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionReg()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_REG;

        $reguser = new Reguser();
        $user = $reguser->findUser('0', $kf->IdPartner.'-'.time(), md5($kf->IdPartner.'-'.time()), $kf->IdPartner, false);

        if ($user) {
            $pay = new CreatePay($user);
            $data = $pay->payActivateCard(0, $kfCard,3, TCBank::$bank, $kf->IdPartner); //Provparams

            //PCI DSS form
            return [
                'status' => 1,
                'id' => (int)$data['IdPay'],
                'url' => $kfCard->GetRegForm($data['IdPay'])
            ];

        }
        return ['status' => 0, 'message' => ''];
    }

    /**
     * Получить данные карты после платежа регистранции карты
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionGet()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_GET;
        $kfCard->load($kf->req, '');
        if (!$kfCard->validate()) {
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }

        $tcBank = new TCBank();
        $tcBank->confirmPay($kfCard->id);

        $card = $kfCard->FindKardByPay($kf->IdPartner, 0);

        //информация по карте
        if ($card) {
            return [
                'status' => 1,
                'card' => [
                    'id' => (int)$card->ID,
                    'num' => (string)$card->CardNumber,
                    'exp' => $card->getMonth() . "/" . $card->getYear()
                ]
            ];
        }
        return ['status' => 0, 'message' => ''];
    }

    /**
     * Удалить карту (у нас)
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \yii\db\Exception
     * @throws ForbiddenHttpException
     */
    public function actionDel()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($kf->req, '');
        if (!$kfCard->validate()) {
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }

        $card = $kfCard->FindKard($kf->IdPartner, 0);
        if ($card) {
            //удалить карту
            $card->IsDeleted = 1;
            $card->save(false);
            return ['status' => 1];
        }
        return ['status' => 0, 'message' => ''];
    }

    /**
     * Автоплатеж
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionPay()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfPay = new KfPay();
        $kfPay->scenario = KfPay::SCENARIO_AUTO;
        $kfPay->load($kf->req, '');
        if (!$kfPay->validate()) {
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        $TcbGate = new TcbGate($kf->IdPartner, TCBank::$AUTOPAYGATE);
        $usl = $kfPay->GetUslugAuto($kf->IdPartner);
        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }

        $kfCard = new KfCard();
        $kfCard->card = $kfPay->card;
        $card = $kfCard->FindKard($kf->IdPartner, 0);
        $user = $kfCard->user;
        if (!$card || !$user) {
            return ['status' => 0, 'message' => 'Карта не найдена'];
        }

        $pay = new CreatePay();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            $paramsExist = $pay->getPaySchetExt($kfPay->extid, $usl, $kf->IdPartner);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'id' => (int)$paramsExist['IdPay'], 'message' => ''];
                } else {
                    return ['status' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        //деление на 7 шлюзов (3 запроса по одной карте в сутки)
        $TcbGate->AutoPayIdGate = $kfPay->GetAutopayGate();
        if (!$TcbGate->AutoPayIdGate) {
            Yii::warning("recarring/auto: нет больше шлюзов");
            return ['status' => 0];
        }

        if ($card && $card->IdPan > 0) {
            $CardToken = new CardToken();
            $cardnum = $CardToken->GetCardByToken($card->IdPan);
        }
        if (empty($cardnum)) {
            Yii::warning("recurrent/pay: empty card", 'mfo');
            return ['status' => 0, 'message' => 'empty card'];
        }

        $kfPay->timeout = 15;
        $params = $pay->payToMfo($user, [$kfPay->extid, $kfPay->card, $TcbGate->AutoPayIdGate], $kfPay, $usl, TCBank::$bank, $kf->IdPartner, $TcbGate->AutoPayIdGate);
        //$params['CardFrom'] = $card->ExtCardIDP;
        $params['card']['number'] = $cardnum;
        $params['card']['holder'] = $card->CardHolder;
        $params['card']['year'] =  $card->getYear();
        $params['card']['month'] = $card->getMonth();

        $payschets = new Payschets();
        $pay->setKardToPaySchet($params['IdPay'], $card->ID);

        //данные карты
        $payschets->SetCardPay($params['IdPay'], [
            'number' => $card->CardNumber,
            'holder' => $card->CardHolder,
            'year' => $card->getYear(),
            'month' => $card->getMonth()
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
            $pay->CancelReq($params['IdPay']);
        }

        return ['status' => 1, 'id' => (int)$params['IdPay']];
    }

    /**
     * Статус платежа
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionState()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

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
