<?php

namespace app\modules\mfo\controllers;

use app\models\antifraud\AntiFraud;
use app\models\antifraud\AntiFraudRefund;
use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\CardToken;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfOut;
use app\models\mfo\MfoReq;
use app\models\mfo\MfoTestError;
use app\models\payonline\Cards;
use app\models\payonline\CreatePay;
use app\models\Payschets;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;


class OutController extends Controller
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
            'paycard' => ['POST'],
            'payacc' => ['POST'],
            'payul' => ['POST'],
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
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());


        $Card = null;
        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($mfo->Req(), '');
        if ($kfCard->validate()) {
            $Card = $kfCard->FindKard($mfo->mfo);
        }

        $kfOut = new KfOut();
        $kfOut->scenario = $Card ? KfOut::SCENARIO_CARDID : KfOut::SCENARIO_CARD;
        $kfOut->load($mfo->Req(), '');
        if (!$kfOut->validate()) {
            Yii::warning("out/paycard: " . $kfOut->GetError(), 'mfo');
            return ['status' => 0];
        }
        if ($Card && $Card->IdPan > 0) {
            $CardToken = new CardToken();
            $kfOut->cardnum = $CardToken->GetCardByToken($Card->IdPan);
        }
        if (empty($kfOut->cardnum)) {
            Yii::warning("out/paycard: empty card", 'mfo');
            return ['status' => 0];
        }

        Yii::warning('out/paycard mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo');

        $TcbGate = new TcbGate($mfo->mfo, TCBank::$OCTGATE);
        $usl = $kfOut->GetUslug($mfo->mfo);
        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0];
        }

        $pay = new CreatePay();
        if (!empty($kfOut->extid)) {
            //проверка на повторный запрос
            $params = $pay->getPaySchetExt($kfOut->extid, $usl, $mfo->mfo);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay']];
                } else {
                    Yii::warning("out/paycard: Нарушение уникальности запроса", 'mfo');
                    return ['status' => 0, 'id' => 0];
                }
            }
        }

        Yii::warning('/out/paycard mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo');

        if ($Card) {
            $token = $Card->IdPan;
        } else {
            //сформировать токен карты, если оплата без регистрации карты
            $cartToken = new CardToken();
            if (($token = $cartToken->CheckExistToken($kfOut->cardnum, 0)) == 0) {
                $token = $cartToken->CreateToken($kfOut->cardnum, 0);
            }
            if ($token === 0) {
                Yii::warning("out/paycard: Ошибка формирования платежа", 'mfo');
                return ['status' => 0];
            }
        }

        //записывает в базу информацию о транзакции.
        $params = $pay->payToCard($kfCard->user, [Cards::MaskCard($kfOut->cardnum), $token, $kfOut->document_id, $kfOut->fullname], $kfOut, $usl, TCBank::$bank, $mfo->mfo);
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
        $anti_fraud = new AntiFraudRefund($params['IdPay'], $mfo->mfo, Cards::MaskCard($kfOut->cardnum));
        if (!$anti_fraud->validate()) {
            $pay->CancelReq($params['IdPay']);
            Yii::warning("out/paycard: Повторный платеж", 'mfo');
            return ['status' => 0];
        }

        /*if (Yii::$app->params['TESTMODE'] == 'Y') {
            //заглушка - тест выплаты на карту
            $test = new MfoTestError();
            if (!$test->TestCancelCards($kfOut->cardnum, $params['IdPay'])) {
                $test->ConfirmOut($kfOut->cardnum, $params['IdPay']);
            }
            return ['status' => 1, 'id' => $params['IdPay']];
        }*/

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
            $pay->CancelReq($params['IdPay']);
        }

        return ['status' => 1, 'id' => (int)$params['IdPay']];
    }

    /**
     * Выплата займа на счет физлица
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionPayacc()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $kfOut = new KfOut();
        $kfOut->scenario = KfOut::SCENARIO_FL;

        $kfOut->load($mfo->Req(), '');
        if (!$kfOut->validate()) {
            Yii::warning("out/payacc: " . $kfOut->GetError(), 'mfo');
            return ['status' => 0];
        }

        $kfOut->descript = str_replace(" ", " ", $kfOut->descript); //0xA0 пробел на 0x20

        $TcbGate = new TcbGate($mfo->mfo, TCBank::$SCHETGATE);
        $usl = $kfOut->GetUslug($mfo->mfo);
        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0];
        }

        $pay = new CreatePay();
        if (!empty($kfOut->extid)) {
            //проверка на повторный запрос
            $params = $pay->getPaySchetExt($kfOut->extid, $usl, $mfo->mfo);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay']];
                } else {
                    Yii::warning("out/payacc: Нарушение уникальности запроса", 'mfo');
                    return ['status' => 0, 'id' => 0];
                }
            }
        }

        Yii::warning('/out/payacc mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo');

        $params = $pay->payToCard(null, [$kfOut->account, $kfOut->bic, $kfOut->fio, $kfOut->descript], $kfOut, $usl, TCBank::$bank, $mfo->mfo);
        $params['name'] = $kfOut->fio;
        $params['inn'] = '';
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
            $pay->CancelReq($params['IdPay']);
        }

        return ['status' => 1, 'id' => (int)$params['IdPay']];
    }

    /**
     * Выплата займа на счет юрлица
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionPayul()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $kfOut = new KfOut();
        $kfOut->scenario = KfOut::SCENARIO_UL;

        $kfOut->load($mfo->Req(), '');
        if (!$kfOut->validate()) {
            Yii::warning("out/payacc: " . $kfOut->GetError(), 'mfo');
            return ['status' => 0];
        }

        $kfOut->descript = str_replace(" ", " ", $kfOut->descript); //0xA0 пробел на 0x20

        $TcbGate = new TcbGate($mfo->mfo, TCBank::$SCHETGATE);
        $usl = $kfOut->GetUslug($mfo->mfo);
        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0];
        }

        $pay = new CreatePay();
        if (!empty($kfOut->extid)) {
            //проверка на повторный запрос
            $params = $pay->getPaySchetExt($kfOut->extid, $usl, $mfo->mfo);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay']];
                } else {
                    Yii::warning("out/payul: Нарушение уникальности запроса", 'mfo');
                    return ['status' => 0, 'id' => 0];
                }
            }
        }

        Yii::warning('/out/payul mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo');

        $params = $pay->payToCard(null, [$kfOut->account, $kfOut->bic, $kfOut->name, $kfOut->inn, $kfOut->kpp, $kfOut->descript], $kfOut, $usl, TCBank::$bank, $mfo->mfo);
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
            $pay->CancelReq($params['IdPay']);
        }

        return ['status' => 1, 'id' => (int)$params['IdPay']];
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
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $IdPay = $mfo->GetReq('id');

        $tcBank = new TCBank();
        $ret = $tcBank->confirmPay($IdPay, $mfo->mfo);
        if ($ret && isset($ret['status'])) {
            $state = ['status' => (int)$ret['status'], 'message' => (string)$ret['message']];
        } else {
            $state = ['status' => 0, 'message' => 'Счет не найден'];
        }
        return $state;
    }

}