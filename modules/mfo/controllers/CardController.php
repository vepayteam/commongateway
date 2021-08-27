<?php

namespace app\modules\mfo\controllers;

use app\models\api\CorsTrait;
use app\models\api\Reguser;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfPay;
use app\models\mfo\MfoReq;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\CardRegForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\mfo\MfoCardRegStrategy;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;
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
            Yii::warning('card/info: нет такой карты', 'mfo');
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
                    'exp' => $Card->getMonth() . '/' . $Card->getYear(),
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
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $cardRegForm = new CardRegForm();
        $cardRegForm->partner = $mfo->getPartner();
        $cardRegForm->load($mfo->Req(), '');

        if(!$cardRegForm->validate()) {
            return ['status' => 0, 'message' => $cardRegForm->GetError()];
        }

        $mutex = new FileMutex();
        if(!empty($cardRegForm->extid)) {
            $mutex->acquire($cardRegForm->getMutexKey(), CardRegForm::MUTEX_TIMEOUT);
        }

        $mfoCardRegStrategy = new MfoCardRegStrategy($cardRegForm);

        try {
            $paySchet = $mfoCardRegStrategy->exec();
            $mutex->release($cardRegForm->getMutexKey());
            return [
                'status' => 1,
                'message' => '',
                'id' => $paySchet->ID,
                'url' => $paySchet->getFromUrl(!empty($cardRegForm->card) ? $cardRegForm->card : null),
            ];
        } catch (CreatePayException $e) {
            $mutex->release($cardRegForm->getMutexKey());
            return ['status' => 0, 'message' => 'Ошибка запроса'];
        } catch (GateException $e) {
            $mutex->release($cardRegForm->getMutexKey());
            return ['status' => 0, 'message' => 'Ошибка запроса'];
        }
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
            Yii::warning('/card/get mfo='. $mfo->mfo . ' ' . $kfCard->GetError(), 'mfo');
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }

        Yii::warning('/card/get mfo='. $mfo->mfo . ' IdPay=' . $kfCard->id . ' type=' .$type, 'mfo');
        
        $paySchet = PaySchet::findOne([$kfCard->id]);
        if (!$paySchet->ID) {
            return ['status' => 0, 'message' => 'Ошибка запроса'];
        }
    
        $statePay = $kfCard->GetPayState();
        if ($statePay == PaySchet::STATUS_ERROR) {
            return ['status' => 2, 'message' => 'Платеж не успешный'];
        }

        if($statePay == PaySchet::STATUS_WAITING) {
            return ['status' => 0, 'message' => 'В обработке'];
        }

        $Card = $kfCard->FindKardByPay($mfo->mfo, $type);

        //информация по карте
        if ($Card && $type == 0) {
            return [
                'status' => 1,
                'message' => '',
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => (string)$Card->CardNumber,
                    'exp' => $Card->getMonth() . '/' . $Card->getYear(),
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
            return ['status' => 1, 'message' => ''];
        }
        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

}
