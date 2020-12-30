<?php

namespace app\modules\mfo\controllers;

use app\models\api\CorsTrait;
use app\models\api\Reguser;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfPay;
use app\models\mfo\MfoReq;
use app\models\payonline\CreatePay;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;
use app\services\auth\HttpHeaderAuthService;
use app\services\cards\KfCardService;


class CardController extends Controller
{
    use CorsTrait;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $this->updateBehaviorsCors($behaviors);
        $behaviors['basicAuth'] = [
            'class' => HttpHeaderAuthService::class(),
        ];
        $behaviors['services'] = [
            'class' => KfCardService::class(),
        ];
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
            'del' => ['POST']
        ];
    }

    /**
     * Информация по карте по ID
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionInfo()
    {

        $type = $this->mfo->GetReq('type');

        $Card = null;
        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($this->mfo->Req(), '');
        if ($kfCard->validate()) {
            $Card = $kfCard->FindKard($this->mfo->mfo, $type);
        }
        if (!$Card) {
            Yii::warning("card/info: нет такой карты", 'mfo');
            return ['status' => 0, 'message' => 'Нет такой карты'];
        }

        //информация и карте
        if ($Card && $type == 0) {
            return [
                'status' => 1,
                'message' => '',
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => (string)$Card->CardNumber,
                    'exp' => $Card->getMonth() . "/" . $Card->getYear(),
                    'holder' => $Card->CardHolder
                ]
            ];
        } elseif ($Card && $type == 1) {
            return [
                'status' => 1,
                'message' => '',
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => (string)$Card->CardNumber,
                    'exp' => '',
                    'holder' => ''
                ]
            ];
        }
        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

    /**
     * Зарегистрировать карту
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws ForbiddenHttpException
     */
    public function actionReg()
    {
        $type = $this->getReq('type');
        $kfCard = $this->getKfCard($this->mfo, KfCard::SCENARIO_REG, "card/reg");
        if ($kfCard->hasErrors()) {
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }
        $mutex = new FileMutex();
        if (!empty($kfCard->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfCard->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $pay = new CreatePay();
            $paramsExist = $pay->getPaySchetExt($kfCard->extid, 1, $this->mfo->mfo);
            if ($paramsExist) {
                return ['status' => 1, 'message' => '', 'id' => (int)$paramsExist['IdPay'], 'url' => $kfCard->GetRegForm($paramsExist['IdPay'])];
            }
        }

        //зарегистрировать карту
        $reguser = new Reguser();
        $user = $reguser->findUser('0', $this->mfo->mfo.'-'.time().random_int(100,999), md5($this->mfo->mfo.'-'.time()), $this->mfo->mfo, false);
        $data['user'] = $user;
        if (!empty($user->Email)) {
            $data['email'] = $user->Email;
        }

        Yii::warning('/card/reg mfo='. $this->mfo->mfo . " type=".$type, 'mfo');

        if ($type == 0) {
            //карта для автоплатежа
            $pay = new CreatePay($user);
            $data = $pay->payActivateCard(0, $kfCard,3, TCBank::$bank, $this->mfo->mfo); //Provparams
            if (!empty($kfCard->extid)) {
                $mutex->release('getPaySchetExt' . $kfCard->extid);
            }
            //PCI DSS
            return [
                'status' => 1,
                'message' => '',
                'id' => $data['IdPay'],
                'url' => $kfCard->GetRegForm($data['IdPay'])
            ];

        } elseif ($type == 1) {
            //карта для выплат
            $pay = new CreatePay($user);
            $data = $pay->payActivateCard(0, $kfCard,3,0, $this->mfo->mfo); //Provparams
            if (!empty($kfCard->extid)) {
                $mutex->release('getPaySchetExt' . $kfCard->extid);
            }

            if (isset($data['IdPay'])) {
                return [
                    'status' => 1,
                    'message' => '',
                    'id' => $data['IdPay'],
                    'url' => $this->mfo->getLinkOutCard($data['IdPay'])
                ];
            }
        }
        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

    /**
     * Информация по карте по платежу
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGet()
    {
        $this->mfo = new MfoReq();
        $this->mfo->LoadData(Yii::$app->request->getRawBody());

        $type = $this->mfo->GetReq('type', 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_GET;
        $kfCard->load($this->mfo->Req(), '');
        if (!$kfCard->validate()) {
            Yii::warning('/card/get mfo='. $this->mfo->mfo . " " . $kfCard->GetError(), 'mfo');
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }

        Yii::warning('/card/get mfo='. $this->mfo->mfo . " IdPay=". $kfCard->id . " type=".$type, 'mfo');

        if ($type == 0) {
            $TcbGate = new TcbGate($this->mfo->mfo, TCBank::$ECOMGATE);
            $tcBank = new TCBank($TcbGate);
            $tcBank->confirmPay($kfCard->id);
        }

        $statePay = $kfCard->GetPayState();
        if ($statePay == 2) {
            return [
                'status' => 2,
                'message' => 'Платеж не успешный'
            ];
        }

        $Card = $kfCard->FindKardByPay($this->mfo->mfo, $type);

        //информация по карте
        if ($Card && $type == 0) {
            return [
                'status' => 1,
                'message' => '',
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => (string)$Card->CardNumber,
                    'exp' => $Card->getMonth() . "/" . $Card->getYear(),
                    'holder' => $Card->CardHolder
                ]
            ];

        } elseif ($Card && $type == 1) {
            return [
                'status' => 1,
                'message' => '',
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => $Card->CardNumber,
                    'exp' => '',
                    'holder' => ''
                ]
            ];
        }
        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionDel()
    {
        $this->mfo = new MfoReq();
        $this->mfo->LoadData(Yii::$app->request->getRawBody());
        $Card = null;
        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($this->mfo->Req(), '');
        if ($kfCard->validate()) {
            $Card = $kfCard->FindKard($this->mfo->mfo, 0);
            if (!$Card) {
                $Card = $kfCard->FindKard($this->mfo->mfo, 1);
            }
        }

        //удалить карту
        if ($Card) {
            $Card->IsDeleted = 1;
            $Card->save(false);
            return ['status' => 1, 'message' => ''];
        }
        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

}
