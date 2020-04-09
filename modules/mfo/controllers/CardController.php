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
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;


class CardController extends Controller
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
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $type = $mfo->GetReq('type');

        $Card = null;
        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($mfo->Req(), '');
        if ($kfCard->validate()) {
            $Card = $kfCard->FindKard($mfo->mfo, $type);
        }
        if (!$Card) {
            Yii::warning("card/info: нет такой карты", 'mfo');
            return ['status' => 0];
        }

        //информация и карте
        if ($Card && $type == 0) {
            return [
                'status' => 1,
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => (string)$Card->CardNumber,
                    'exp' => $Card->getMonth() . "/" . $Card->getYear()
                ]
            ];
        } elseif ($Card && $type == 1) {
            return [
                'status' => 1,
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => (string)$Card->CardNumber,
                    'exp' => ''
                ]
            ];
        }
        return ['status' => 0];
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
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $type = $mfo->GetReq('type');

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_REG;
        $kfCard->load($mfo->Req(),'');
        if (!$kfCard->validate()) {
            Yii::warning("card/reg: " . $kfCard->GetError());
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }

        if (!empty($kfCard->extid)) {
            //проверка на повторный запрос
            $pay = new CreatePay();
            $paramsExist = $pay->getPaySchetExt($kfCard->extid, 1, $mfo->mfo);
            if ($paramsExist) {
                return ['status' => 1, 'message' => '', 'id' => (int)$paramsExist['IdPay'], 'url' => $kfCard->GetRegForm($paramsExist['IdPay'])];
            }
        }

        //зарегистрировать карту
        $reguser = new Reguser();
        $user = $reguser->findUser('0', $mfo->mfo.'-'.time(), md5($mfo->mfo.'-'.time()), $mfo->mfo, false);
        $data['user'] = $user;
        if (!empty($user->Email)) {
            $data['email'] = $user->Email;
        }

        Yii::warning('/card/reg mfo='. $mfo->mfo . " type=".$type, 'mfo');

        if ($type == 0) {
            //карта для автоплатежа
            $pay = new CreatePay($user);
            $data = $pay->payActivateCard(0, $kfCard,3, TCBank::$bank, $mfo->mfo); //Provparams
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
            $data = $pay->payActivateCard(0, $kfCard,3,0, $mfo->mfo); //Provparams

            if (isset($data['IdPay'])) {
                return [
                    'status' => 1,
                    'id' => $data['IdPay'],
                    'url' => $mfo->getLinkOutCard($data['IdPay'])
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
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $type = $mfo->GetReq('type', 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_GET;
        $kfCard->load($mfo->Req(), '');
        if (!$kfCard->validate()) {
            Yii::warning('/card/get mfo='. $mfo->mfo . " IdPay=0", 'mfo');
            return ['status' => 0];
        }

        Yii::warning('/card/get mfo='. $mfo->mfo . " IdPay=". $kfCard->id . " type=".$type, 'mfo');

        if ($type == 0) {
            $TcbGate = new TcbGate($mfo->mfo, TCBank::$ECOMGATE);
            $tcBank = new TCBank($TcbGate);
            $tcBank->confirmPay($kfCard->id);
        }

        $statePay = $kfCard->GetPayState();
        if ($statePay == 2) {
            return [
                'status' => 2
            ];
        }

        $Card = $kfCard->FindKardByPay($mfo->mfo, $type);

        //информация по карте
        if ($Card && $type == 0) {
            return [
                'status' => 1,
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => (string)$Card->CardNumber,
                    'exp' => $Card->getMonth() . "/" . $Card->getYear()
                ]
            ];

        } elseif ($Card && $type == 1) {
            return [
                'status' => 1,
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => $Card->CardNumber,
                    'exp' => ''
                ]
            ];
        }
        return ['status' => 0];
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
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());
        $Card = null;
        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($mfo->Req(), '');
        if ($kfCard->validate()) {
            $Card = $kfCard->FindKard($mfo->mfo, 0);
            if (!$Card) {
                $Card = $kfCard->FindKard($mfo->mfo, 1);
            }
        }

        //удалить карту
        if ($Card) {
            $Card->IsDeleted = 1;
            $Card->save(false);
            return ['status' => 1];
        }
        return ['status' => 0];
    }

}
