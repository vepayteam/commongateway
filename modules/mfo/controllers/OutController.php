<?php

namespace app\modules\mfo\controllers;

use app\models\antifraud\AntiFraud;
use app\models\antifraud\AntiFraudRefund;
use app\models\api\CorsTrait;
use app\models\bank\BankMerchant;
use app\models\bank\Banks;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\crypt\CardToken;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfOut;
use app\models\mfo\MfoReq;
use app\models\mfo\MfoTestError;
use app\models\payonline\Cards;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\mfo\MfoOutCardStrategy;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;
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
        Yii::warning("mfo/out/paycard Authorization mfo=$mfo->mfo", 'mfo_out_paycard');

        $outCardPayForm = new OutCardPayForm();
        $outCardPayForm->partner = $mfo->getPartner();
        $outCardPayForm->load($mfo->Req(), '');
        if (!$outCardPayForm->validate()) {
            Yii::warning("out/paycard: " . $outCardPayForm->GetError(), 'mfo');
            return ['status' => 0, 'message' => $outCardPayForm->GetError()];
        }

        $mfoOutCardStrategy = new MfoOutCardStrategy($outCardPayForm);
        $paySchet = $mfoOutCardStrategy->exec();



        $Card = null;
        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($mfo->Req(), '');
        if ($kfCard->validate()) {
            $Card = $kfCard->FindKard($mfo->mfo);
            Yii::warning("mfo/out/paycard Validate KfCard mfo=$mfo->mfo kfCard=$Card->ID", 'mfo_out_paycard');
        }
        $kfOut = new KfOut();
        $kfOut->scenario = $Card ? KfOut::SCENARIO_CARDID : KfOut::SCENARIO_CARD;
        $kfOut->load($mfo->Req(), '');
        Yii::warning('Validate KfOut mfo/out/paycard', 'mfo_out_paycard');
        if (!$kfOut->validate()) {
            Yii::warning("out/paycard: " . $kfOut->GetError(), 'mfo');
            return ['status' => 0, 'message' => $kfOut->GetError()];
        }
        if ($Card && $Card->IdPan > 0) {
            $CardToken = new CardToken();
            $kfOut->cardnum = $CardToken->GetCardByToken($Card->IdPan);
        }
        if (empty($kfOut->cardnum)) {
            Yii::warning("out/paycard: empty card", 'mfo');
            return ['status' => 0, 'message' => 'empty card'];
        }

        Yii::warning('out/paycard mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo');

        $bank = BankMerchant::GetWorkBankOut();

        $typeUsl = TU::$TOCARD;
        Yii::warning('mfo/out/paycard Fet bank gate mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo_out_paycard');
        $bankGate = BankMerchant::Gate($mfo->mfo, $bank, $typeUsl);
        $usl = $kfOut->GetUslug($mfo->mfo);
        if (!$usl || !$bankGate || !$bankGate->IsGate()) {
            return ['status' => 0, 'message' => 'Нет шлюза'];
        }

        $pay = new CreatePay();
        Yii::warning('mfo/out/paycard CreatePay mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo_out_paycard');
        $mutex = new FileMutex();
        if (!empty($kfOut->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfOut->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $params = $pay->getPaySchetExt($kfOut->extid, $usl, $mfo->mfo);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
                } else {
                    Yii::warning("out/paycard: Нарушение уникальности запроса", 'mfo');
                    return ['status' => 0, 'id' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        Yii::warning('/out/paycard mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo');

        if ($Card) {
            $token = $Card->IdPan;
        } else {
            //сформировать токен карты, если оплата без регистрации карты
            Yii::warning('mfo/out/paycard CardToken mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo_out_paycard');
            $cartToken = new CardToken();
            if (($token = $cartToken->CheckExistToken($kfOut->cardnum, 0)) == 0) {
                $token = $cartToken->CreateToken($kfOut->cardnum, 0, '');
            }
            if ($token === 0) {
                Yii::warning("out/paycard: Ошибка формирования платежа", 'mfo');
                return ['status' => 0, 'message' => 'Ошибка формирования платежа'];
            }
        }

        Yii::warning('mfo/out/paycard payToCard ', 'mfo_out_paycard');
        //записывает в базу информацию о транзакции.
        $params = $pay->payToCard($kfCard->user, [Cards::MaskCard($kfOut->cardnum), $token, $kfOut->document_id, $kfOut->fullname], $kfOut, $usl, TCBank::$bank, $mfo->mfo);
        if (!empty($kfOut->extid)) {
            $mutex->release('getPaySchetExt' . $kfOut->extid);
        }
        $params['CardNum'] = $kfOut->cardnum;

        $payschets = new Payschets();
        Yii::warning('mfo/out/paycard SetCardPay mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo_out_paycard');
        //данные карты
        $payschets->SetCardPay($params['IdPay'], [
            'number' => $kfOut->cardnum,
            'holder' => '',
            'month' => 0,
            'year' => 0
        ]);

        Yii::warning('mfo/out/paycard AntiFraudRefund mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo_out_paycard');
        //антифрод должен рабоатть после записи в базу.
        $anti_fraud = new AntiFraudRefund($params['IdPay'], $mfo->mfo, Cards::MaskCard($kfOut->cardnum));
        if (!$anti_fraud->validate()) {
            $pay->CancelReq($params['IdPay'],'Повторный платеж');
            Yii::warning("out/paycard: Повторный платеж", 'mfo');
            return ['status' => 0, 'message' => 'Повторный платеж'];
        }

        /*if (Yii::$app->params['TESTMODE'] == 'Y') {
            //заглушка - тест выплаты на карту
            $test = new MfoTestError();
            if (!$test->TestCancelCards($kfOut->cardnum, $params['IdPay'])) {
                $test->ConfirmOut($kfOut->cardnum, $params['IdPay']);
            }
            return ['status' => 1, 'id' => $params['IdPay']];
        }*/

        Yii::warning('mfo/out/paycard Find Partner mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo_out_paycard');
        $partner = Partner::findOne(['ID' => $mfo->mfo]);
        $bankClass = Banks::getBankClassByTransferToCard($partner);
        $payschets->ChangeBank($params['IdPay'], $bankClass::$bank);

        Yii::warning('mfo/out/paycard Get BankMerchant mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo_out_paycard');
        $merchBank = BankMerchant::Get($bankClass::$bank, $bankGate);
        $ret = $merchBank->transferToCard($params);
        if ($ret && $ret['status'] == 1) {
            //сохранение номера транзакции
            Yii::warning('mfo/out/paycard SetBankTransact mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo_out_paycard');
            $payschets->SetBankTransact([
                'idpay' => $params['IdPay'],
                'trx_id' => $ret['transac'],
                'url' => ''
            ]);

        } else {
            Yii::error('mfo/out/paycard CancelReq mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo_out_paycard');
            $pay->CancelReq($params['IdPay'],'Платеж не проведен');
        }

        return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
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
            return ['status' => 0, 'message' => $kfOut->GetError()];
        }

        $kfOut->descript = str_replace(" ", " ", $kfOut->descript); //0xA0 пробел на 0x20

        $bank = BankMerchant::GetWorkBankOut();

        $typeUsl = TU::$TOSCHET;
        $bankGate = BankMerchant::Gate($mfo->mfo, $bank, $typeUsl);

        $usl = $kfOut->GetUslug($mfo->mfo);
        if (!$usl || !$bankGate || !$bankGate->IsGate()) {
            return ['status' => 0, 'message' => 'Нет шлюза'];
        }

        $pay = new CreatePay();
        $mutex = new FileMutex();
        if (!empty($kfOut->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfOut->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $params = $pay->getPaySchetExt($kfOut->extid, $usl, $mfo->mfo);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
                } else {
                    Yii::warning("out/payacc: Нарушение уникальности запроса", 'mfo');
                    return ['status' => 0, 'id' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        Yii::warning('/out/payacc mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo');

        $params = $pay->payToCard(null, [$kfOut->account, $kfOut->bic, $kfOut->fio, $kfOut->descript], $kfOut, $usl, TCBank::$bank, $mfo->mfo);
        if (!empty($kfOut->extid)) {
            $mutex->release('getPaySchetExt' . $kfOut->extid);
        }
        $params['name'] = $kfOut->fio;
        $params['inn'] = '';
        $params['bic'] = $kfOut->bic;
        $params['account'] = $kfOut->account;
        $params['descript'] = $kfOut->descript;

        $merchBank = BankMerchant::Get($bank, $bankGate);
        $ret = $merchBank->transferToAccount($params);
        if ($ret && $ret['status'] == 1) {
            //сохранение номера транзакции
            $payschets = new Payschets();
            $payschets->SetBankTransact([
                'idpay' => $params['IdPay'],
                'trx_id' => $ret['transac'],
                'url' => ''
            ]);

        } else {
            $pay->CancelReq($params['IdPay'],'Платеж не проведен');
        }

        return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
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
            return ['status' => 0, 'message' => $kfOut->GetError()];
        }

        $kfOut->descript = str_replace(" ", " ", $kfOut->descript); //0xA0 пробел на 0x20

        $bank = BankMerchant::GetWorkBankOut();

        $typeUsl = TU::$TOSCHET;
        $bankGate = BankMerchant::Gate($mfo->mfo, $bank, $typeUsl);

        $usl = $kfOut->GetUslug($mfo->mfo);
        if (!$usl || !$bankGate || !$bankGate->IsGate()) {
            return ['status' => 0, 'message' => 'Нет шлюза'];
        }

        $pay = new CreatePay();
        $mutex = new FileMutex();
        if (!empty($kfOut->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfOut->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $params = $pay->getPaySchetExt($kfOut->extid, $usl, $mfo->mfo);
            if ($params) {
                if ($kfOut->amount == $params['sumin']) {
                    return ['status' => 1, 'id' => (int)$params['IdPay'], 'message' => ''];
                } else {
                    Yii::warning("out/payul: Нарушение уникальности запроса", 'mfo');
                    return ['status' => 0, 'id' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

        Yii::warning('/out/payul mfo=' . $mfo->mfo . " sum=" . $kfOut->amount . " extid=" . $kfOut->extid, 'mfo');

        $params = $pay->payToCard(null, [$kfOut->account, $kfOut->bic, $kfOut->name, $kfOut->inn, $kfOut->kpp, $kfOut->descript], $kfOut, $usl, TCBank::$bank, $mfo->mfo);
        if (!empty($kfOut->extid)) {
            $mutex->release('getPaySchetExt' . $kfOut->extid);
        }
        $params['name'] = $kfOut->name;
        $params['inn'] = trim($kfOut->inn);
        $params['kpp'] = $kfOut->kpp;
        $params['bic'] = $kfOut->bic;
        $params['account'] = $kfOut->account;
        $params['descript'] = $kfOut->descript;

        $merchBank = BankMerchant::Get($bank, $bankGate);
        $ret = $merchBank->transferToAccount($params);
        if ($ret && $ret['status'] == 1) {
            //сохранение номера транзакции
            $payschets = new Payschets();
            $payschets->SetBankTransact([
                'idpay' => $params['IdPay'],
                'trx_id' => $ret['transac'],
                'url' => ''
            ]);

        } else {
            $pay->CancelReq($params['IdPay'],'Платеж не проведен');
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
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $IdPay = $mfo->GetReq('id');
        $paySchet = PaySchet::findOne(['ID' => $IdPay]);
        if(!$paySchet) {
            return ['status' => 0, 'message' => 'Счет не найден'];
        } else {
            $status = (int)$paySchet->Status;
            $message = $paySchet->ErrorInfo;

            // Если платеж ожидает проверку статуса в очереди, пользователю возвращаем статус 0, чтобы соотв документации
            if($status == PaySchet::STATUS_WAITING_CHECK_STATUS) {
                $status = PaySchet::STATUS_WAITING;
                $message = 'Ожидается обновление статуса';
            }

            return [
                'status' => $status,
                'message' => $message,
                'rc' => $paySchet->RCCode,
            ];
        }
    }
}
