<?php

namespace app\modules\mfo\controllers;

use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\mfo\IdentificationUser;
use app\models\mfo\MfoReq;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class IdentController extends Controller
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
            'index' => ['POST'],
            'state' => ['POST'],
        ];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionIndex()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $inId = $mfo->GetReq('id');
        $params = $mfo->GetReqs(['fam','nam','otc','paspser','paspnum','paspdate','paspcode','paspvid','phone','phonecode','birth','inn','snils']);

        $iu = new IdentificationUser();
        $id = $iu->FindReq($inId, $mfo->mfo);
        if (!$id) {
            $id = $iu->Create(null, $mfo->mfo, $params);
        }

        if ($id) {
            $TcbGate = new TcbGate($mfo->mfo, TCBank::$ECOMGATE);
            $tcBank = new TCBank($TcbGate);
            $ret = $tcBank->personIndent($id, $params);
            if ($ret['status'] == 1) {
                $iu->SetTansac($id, $ret['transac']);
                return ['status' => $ret['status'], 'id' => $id];
            }
        }
        return ['status' => 0];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionState()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $inId = $mfo->GetReq('id');

        $iu = new IdentificationUser();
        $id = $iu->FindReq($inId, $mfo->mfo);

        if ($id) {
            if ($iu->StateOp == 0) {
                $TcbGate = new TcbGate($mfo->mfo, TCBank::$ECOMGATE);
                $tcBank = new TCBank($TcbGate);
                $ret = $tcBank->personGetIndentResult($id);
                if ($ret) {
                    $iu->SetStatus($id, $ret['status'], $ret['message']);
                }
                return ['status' => $ret['status'], 'message' => $ret['message']];
            } elseif ($iu->StateOp > 0) {
                return ['status' => $iu->StateOp, 'message' => $iu->StateOp];
            }
        }

        return ['status' => 0];

    }

}
