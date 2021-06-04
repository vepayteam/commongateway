<?php

namespace app\modules\mfo\controllers;

use app\Api\Client\AbstractClient;
use app\Api\Client\Client;
use app\models\payonline\Partner;
use app\services\balance\Balance;
use app\services\balance\response\BalanceResponse;
use GuzzleHttp\RequestOptions;
use Yii;
use app\models\api\CorsTrait;
use app\models\mfo\MfoReq;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class AccountController extends Controller
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
            'balance' => ['POST'],
            'statements' => ['POST'],
        ];
    }

    /**
     * Баланс счета
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionBalance(): array
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());
        if (!$mfo->mfo) {
            return ['status' => 0, 'message' => BalanceResponse::PARTNER_NOT_FOUND_ERROR_MSG];
        }
        $partner = Partner::findOne(['ID' => $mfo->mfo]);
        $balance = new Balance();
        $balance->setAttributes([
            'partner' => $partner
        ]);
        if (!$balance->validate()) {
            return ['status' => 0, 'message' => BalanceResponse::PARTNER_NOT_FOUND_ERROR_MSG];
        }
        return (array)$balance->getAllBanksBalance();
    }
}
