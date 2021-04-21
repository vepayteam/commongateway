<?php

namespace app\modules\mfo\controllers;

use app\models\bank\TcbGate;
use app\models\mfo\MfoBalance;
use app\models\payonline\Partner;
use app\services\balance\Balance;
use app\services\payment\models\repositories\BankRepository;
use Yii;
use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\kfapi\KfBalance;
use app\models\mfo\MfoReq;
use yii\base\BaseObject;
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
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionBalance()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());
        if (!$mfo->mfo) {
            return ['status' => 0, 'message' => 'Партнер не найден'];
        }

        $partner = Partner::findOne(['ID' => $mfo->mfo]);
        $mfoBalance = new Balance();
        $mfoBalance->setAttributes([
            'partner' => $partner
        ]);
        if (!$mfoBalance->validate()) {
            return ['status' => 0, 'message' => 'Партнер не найден'];
        }
        $allBanksBalance = $mfoBalance->getAllBanksBalance($mfo);

        /** @deprecated TODO: move tkbBanks balance realisation to adapter getBalance method */
        $kfBal = new KfBalance();
        $kfBal->load($mfo->Req(), '');
        if (empty($kfBal->account)) {
            $kfBal->setAttributes([
                'account' => $partner->SchetTcb
            ]);
        }
        $bal = null;
        $tkbBankBalance = [];
        if ($kfBal->validate()) {
            $bal = $kfBal->GetBalance($partner);
            $tkbBank = BankRepository::getBankById(TCBank::$bank);
            $tkbBankBalance[] = [
                'amount' => $bal,
                'currency' => 'RUB',
                'bank_name' => $tkbBank->getName()
            ];
        }
        return [
            'status' => $allBanksBalance->status,
            'message' => $allBanksBalance->message,
            'amount' => $bal ? round($bal, 2) : null, /** @deprecated TODO: remove **/
            'balance' => array_merge((array)$allBanksBalance->balance, $tkbBankBalance),
        ];
    }
}
