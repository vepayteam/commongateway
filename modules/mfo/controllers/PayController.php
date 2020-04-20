<?php

namespace app\modules\mfo\controllers;

use app\models\api\CorsTrait;
use app\models\bank\BankMerchant;
use app\models\bank\MTSBank;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\CardToken;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfPay;
use app\models\mfo\MfoReq;
use app\models\payonline\CreatePay;
use app\models\Payschets;
use app\models\TU;
use Yii;
use yii\helpers\VarDumper;
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

        $kfPay = new KfPay();
        $kfPay->scenario = KfPay::SCENARIO_FORM;
        $kfPay->load($mfo->Req(), '');
        if (!$kfPay->validate()) {
            Yii::warning("pay/lk: ".$kfPay->GetError());
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        Yii::warning('/pay/lk mfo='. $mfo->mfo . " sum=".$kfPay->amount . " extid=".$kfPay->extid, 'mfo');

        $bank = MTSBank::$bank;

        $typeUsl = $kfPay->IsAftGate($mfo->mfo) ? TU::$POGASHATF : TU::$POGASHECOM;
        $bankGate = BankMerchant::Gate($mfo->mfo, $bank, $typeUsl);
        $usl = $kfPay->GetUslug($mfo->mfo, $typeUsl);
        if (!$usl || !$bankGate || !$bankGate->IsGate()) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }

        $pay = new CreatePay();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            $paramsExist = $pay->getPaySchetExt($kfPay->extid, $usl, $mfo->mfo);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'message' => '', 'id' => (int)$paramsExist['IdPay'], 'url' => $kfPay->GetPayForm($paramsExist['IdPay'])];
                } else {
                    Yii::warning("pay/lk: Нарушение уникальности запроса");
                    return ['status' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }
        $params = $pay->payToMfo(null, [$kfPay->document_id, $kfPay->fullname], $kfPay, $usl, $bank, $mfo->mfo,0);
        //PCI DSS
        return [
            'status' => 1,
            'message' => '',
            'id' => (int)$params['IdPay'],
            'url' => $kfPay->GetPayForm($params['IdPay'])
        ];
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
            return ['status' => 0];

        }
        $Card = $kfCard->FindKard($mfo->mfo,0);
        if (!$Card) {
            Yii::warning("pay/auto: нет такой карты");
            return ['status' => 0];
        }

        $kfPay = new KfPay();
        $kfPay->scenario = KfPay::SCENARIO_AUTO;
        $kfPay->load($mfo->Req(), '');
        if (!$kfPay->validate()) {
            Yii::warning("pay/auto: ".$kfPay->GetError());
            return ['status' => 0];
        }

        $TcbGate = new TcbGate($mfo->mfo, TCBank::$AUTOPAYGATE);
        $usl = $kfPay->GetUslugAuto($mfo->mfo);

        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0];
        }

        Yii::warning('/pay/auto mfo='. $mfo->mfo . " sum=".$kfPay->amount . " extid=".$kfPay->extid, 'mfo');

        $pay = new CreatePay();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            $paramsExist = $pay->getPaySchetExt($kfPay->extid, $usl, $mfo->mfo);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'id' => (int)$paramsExist['IdPay']];
                } else {
                    Yii::warning("pay/auto: Нарушение уникальности запроса");
                    return ['status' => 0, 'id' => 0];
                }
            }
        }

        //деление на 7 шлюзов (3 запроса по одной карте в сутки)
        $TcbGate->AutoPayIdGate = $kfPay->GetAutopayGate();
        if (!$TcbGate->AutoPayIdGate) {
            Yii::warning("pay/auto: нет больше шлюзов");
            return ['status' => 0];
        }
        if ($Card && $Card->IdPan > 0) {
            $CardToken = new CardToken();
            $cardnum = $CardToken->GetCardByToken($Card->IdPan);
        }
        if (empty($cardnum)) {
            Yii::warning("pay/auto: empty card", 'mfo');
            return ['status' => 0];
        }

        $kfPay->timeout = 30;
        $params = $pay->payToMfo($kfCard->user, [$kfPay->extid, $Card->ID, $TcbGate->AutoPayIdGate], $kfPay, $usl, TCBank::$bank, $mfo->mfo, $TcbGate->AutoPayIdGate);
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
            $pay->CancelReq($params['IdPay']);
        }

        return ['status' => 1, 'id' => (int)$params['IdPay']];
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
                return ['status' => (int)$ret['status'], 'message' => (string)$ret['message']];
            }
        }
        return ['status' => 0, 'message' => 'Счет не найден'];
    }

}
