<?php

namespace app\modules\kfapi\controllers;

use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfBalance;
use app\models\kfapi\KfRequest;
use app\models\kfapi\KfStatement;
use app\models\mfo\MfoBalance;
use app\models\mfo\MfoTestError;
use app\models\payonline\Partner;
use app\services\statements\models\StatementsAccount;
use app\services\statements\StatementsService;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
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
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $kfBal = new KfBalance();
        $kfBal->load($kf->req, '');
        if (!$kfBal->validate()) {
            return ['status' => 0, 'message' => $kfBal->GetError()];
        }

        $bal = $kfBal->GetBalance($kf->partner);
        if ($bal !== null) {
            $state = [
                'status' => 1,
                'message' => '',
                'amount' => round($bal, 2),
                'insumm' => round($kfBal->GetInSums($kf->IdPartner) / 100.0, 2),
                'outsumm' => round($kfBal->GetOutSums($kf->IdPartner) / 100.0, 2)
            ];
        } else {
            $state = ['status' => 0, 'message' => 'Ошибка запроса', 'amount' => '0.00', 'insumm' => '0.00', 'outsumm' => '0.00'];
        }
        /*$TcbGate = new TcbGate($kf->IdPartner, TCBank::$AFTGATE);
        $tcBank = new TCBank($TcbGate);
        $ret = $tcBank->getBalanceAcc(['account' => $kfBal->account]);
        //$ret = $tcBank->getBalance();
        if ($ret && isset($ret['status']) && $ret['status'] == 1) {
            $state = [
                'status' => 1,
                'message' => '',
                'amount' => round($ret['amount'], 2),
                'insumm' => round($kfBal->GetInSums($kf->IdPartner) / 100.0, 2),
                'outsumm' => round($kfBal->GetOutSums($kf->IdPartner) / 100.0, 2)
            ];
        } else {
            $state = ['status' => 0, 'message' => $ret['message'], 'amount' => '0.00', 'insumm' => '0.00', 'outsumm' => '0.00'];
        }*/
        return $state;
    }

    /**
     * Выписка по счету
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionStatements()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $kfStatm = new KfStatement();
        $kfStatm->partner = $kf->partner;
        $kfStatm->load($kf->req, '');
        if (!$kfStatm->validate()) {
            return ['status' => 0, 'message' => $kfStatm->GetError()];
        }

        $statements = $this->getStatementsService()->getBanksStatements($kfStatm);
        $return = [];
        /** @var StatementsAccount $statement */
        foreach ($statements as $statement) {
            $return[] = [
                'id' => $statement->BnkId,
                'number' => $statement->NumberPP,
                'date' => date('Y-m-d\TH:i:s', $statement->DatePP),
                'datedoc' => date('Y-m-d\TH:i:s', $statement->DateDoc),
                'summ' => round(($statement->SummPP + $statement->SummComis)/100.0,2),
                'description' => $statement->Description,
                'iscredit' => $statement->IsCredit ? true : false, //true - пополнение счета
                'name' => $statement->Name,
                'inn' => $statement->Inn,
                'kpp' => '',
                'bic' => $statement->Bic,
                'bank' => $statement->Bank,
                'bankaccount' => $statement->BankAccount,
                'account' => $statement->Account,
            ];
        }
        return [
            'status' => 1,
            'message' => '',
            'statements' => $return
        ];
    }

    /**
     * @return StatementsService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getStatementsService()
    {
        return Yii::$container->get('StatementsService');
    }
}
