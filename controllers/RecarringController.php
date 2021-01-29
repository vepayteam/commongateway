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
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\payment_strategies\mfo\MfoAutoPayStrategy;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;
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

        $autoPayForm = new AutoPayForm();
        $autoPayForm->partner = $kf->partner;
        $autoPayForm->load($kf->req, '');
        if(!$autoPayForm->validate()) {
            Yii::warning("mfo/pay/auto: ошибка валидации формы");
            return ['status' => 0, 'message' => $autoPayForm->getError()];
        }
        Yii::warning("mfo/pay/auto AutoPayForm extid=$autoPayForm->extid amount=$autoPayForm->amount", 'mfo');
        // рубли в копейки
        $autoPayForm->amount *= 100;

        $mfoAutoPayStrategy = new MfoAutoPayStrategy($autoPayForm);
        try {
            $paySchet = $mfoAutoPayStrategy->exec();
        } catch (CreatePayException $e) {
            return ['status' => 2, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 2, 'message' => $e->getMessage()];
        }

        return ['status' => 1, 'message' => '', 'id' => $paySchet->ID];
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
