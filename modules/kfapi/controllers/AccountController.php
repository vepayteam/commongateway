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
            'statements' => ['POST'],
        ];
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
        $kfStatm->load($kf->req, '');
        if (!$kfStatm->validate()) {
            return ['status' => 0, 'message' => $kfStatm->GetError()];
        }

        $TypeAcc = 0;
        if ($kf->partner->SchetTcbNominal == $kfStatm->account) {
            //номинальный
            $TypeAcc = 2;
        } elseif ($kf->partner->SchetTcbTransit == $kfStatm->account) {
            //транзитный на погашение
            $TypeAcc = 1;
        }

        $result = StatementsService::GetBankStatements($kf->partner, $TypeAcc, strtotime($kfStatm->dayfrom), strtotime($kfStatm->dayto));

        $ret = [];
        foreach ($result as $row) {
            $ret[] = [
                'id' => $row->BnkId,
                'number' => $row->NumberPP,
                'date' => date('Y-m-d\TH:i:s', $row->DatePP),
                'datedoc' => date('Y-m-d\TH:i:s', $row->DateDoc),
                'summ' => round(($row->SummPP + $row->SummComis)/100.0,2),
                'description' => $row->Description,
                'iscredit' => $row->IsCredit ? true : false, //true - пополнение счета
                'name' => $row->Name,
                'inn' => $row->Inn,
                'kpp' => '',
                'bic' => $row->Bic,
                'bank' => $row->Bank,
                'bankaccount' => $row->BankAccount,
                'account' => $row->Account
            ];
        }
        $state = [
            'status' => 1,
            'message' => '',
            'statements' => $ret
        ];

        /*if (Yii::$app->params['TESTMODE'] == 'Y') {
            //заглушка - тест выписка
            $test = new MfoTestError();
            return [
                'status' => 1,
                'message' => '',
                'statements' => $test->TestStatements()
            ];
        }

        $tcBank = new TCBank(TCBank::$AFTGATE, null, 1, $kf->GetGates());

        if ($kf->partner->SchetTcbNominal == $kfStatm->account) {
            //номинальный - по другим данным
            $ret = $tcBank->getStatementNominal(['account' => $kfStatm->account, 'datefrom' => $kfStatm->dayfrom, 'dateto' => $kfStatm->dayto]);
            if ($ret && isset($ret['status']) && $ret['status'] == 1) {
                $state = [
                    'status' => 1,
                    'message' => '',
                    'statements' => $kfStatm->ParseSatementsNominal($ret['statements'])
                ];
            } else {
                $state = ['status' => 0, 'message' => $ret['message'], 'statements' => []];
            }
        } else {
            //транзитный
            $ret = $tcBank->getStatement(['account' => $kfStatm->account, 'datefrom' => $kfStatm->dayfrom, 'dateto' => $kfStatm->dayto]);
            if ($ret && isset($ret['status']) && $ret['status'] == 1) {
                $state = [
                    'status' => 1,
                    'message' => '',
                    'statements' => $kfStatm->ParseSatements($ret['statements'])
                ];
            } else {
                $state = ['status' => 0, 'message' => $ret['message'], 'statements' => []];
            }
        }*/
        return $state;

    }
}