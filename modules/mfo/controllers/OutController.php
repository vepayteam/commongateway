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
use app\services\payment\exceptions\CardTokenException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\OutCardPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\mfo\MfoOutCardStrategy;
use Vepay\Gateway\Client\Validator\ValidationException;
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


        try {
            $outCardPayForm = new OutCardPayForm();
            $outCardPayForm->partner = $mfo->getPartner();
            $outCardPayForm->load($mfo->Req(), '');
            if (!$outCardPayForm->validate()) {
                Yii::warning("out/paycard: " . $outCardPayForm->GetError(), 'mfo');
                return ['status' => 0, 'message' => $outCardPayForm->GetError()];
            }
            // рубли в коп
            $outCardPayForm->amount *= 100;
            $mfoOutCardStrategy = new MfoOutCardStrategy($outCardPayForm);
            $paySchet = $mfoOutCardStrategy->exec();
            return ['status' => 1, 'id' => $paySchet->ID, 'message' => ''];
        } catch (CardTokenException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (CreatePayException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (ValidationException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }
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
            Yii::warning("out/payacc: нет шлюза. mfo=$mfo->mfo uslugatovarId=$usl bankId=$bank");
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
        $params['inn'] = $kfOut->inn;
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
            Yii::warning("out/payacc: нет шлюза. mfo=$mfo->mfo uslugatovarId=$usl bankId=$bank");
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
