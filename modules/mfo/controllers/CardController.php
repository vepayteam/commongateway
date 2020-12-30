<?php

namespace app\modules\mfo\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfCard;
use app\services\auth\HttpHeaderAuthService;
use app\services\cards\KfCardService;
use app\services\card\RegCardService;


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
     * @return array
     */
    public function actionInfo()
    {
        $kfCard = $this->getKfCard($this->mfo, KfCard::SCENARIO_INFO, "card/reg");
        if ($kfCard->hasErrors()) {
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }
        $type = $this->mfo->GetReq('type');
        $Card = $kfCard->FindKard($this->mfo->mfo, $type);
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
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionReg()
    {
        $kfCard = $this->getKfCard($this->mfo, KfCard::SCENARIO_REG, "card/reg");
        if ($kfCard->hasErrors()) {
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }
        $reg = new RegCardService();
        $result = $reg->exec($this->mfo, $kfCard);
        if (isset($result['status'])) {
            return ['status' => $result['status'], 'message' => $result['message'], 'id' => $result['pay_schet_id'], 'url' => $kfCard->GetRegForm($result['pay_schet_id'])];
        }
        return ['status' => 0, 'message' => 'Ошибка запроса'];
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionGet()
    {
        $kfCard = $this->getKfCard($this->mfo, KfCard::SCENARIO_GET, "card/reg");
        if ($kfCard->hasErrors()) {
            return ['status' => 0, 'message' => $kfCard->GetError()];
        }
        $type = $this->getReq('type');
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
     */
    public function actionDel()
    {
        $Card = null;
        $kfCard = $this->getKfCard($this->mfo, KfCard::SCENARIO_INFO, "card/reg");
        if (!$kfCard->hasErrors()) {
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
