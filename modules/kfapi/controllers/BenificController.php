<?php

namespace app\modules\kfapi\controllers;

use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfRequest;
use app\models\kfapi\KfBenific;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;


class BenificController extends Controller
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
            'reg' => ['POST'],
        ];
    }

    /**
     * Регистрация бенифициаров
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionReg()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        $KfBenific = new KfBenific();
        $KfBenific->load($kf->req, '');
        if (!$KfBenific->validate()) {
            return ['status' => 0, 'message' => $KfBenific->GetError()];
        }

        if (empty($kf->partner->SchetTcbNominal)) {
            return ['status' => 0, 'message' => 'Номинальный счет не найден'];
        }

        $TcbGate = new TcbGate($kf->IdPartner, TCBank::$AFTGATE);
        $tcBank = new TCBank($TcbGate);
        $ret = $tcBank->RegisterBenificiar([
            'req' => $KfBenific->GetSoapForm($kf->partner)
        ]);

        if (isset($ret['status']) && $ret['status'] == 1) {
            $KfBenific->setAttributes(['result' => $ret['soap']]);
            $ret = $KfBenific->ParseResult();
            return ['status' => $ret['error'] == 0 ? 1 : 0, 'message' => $ret['message']];
        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];

    }

    /**
     * Регистрация бенифициаров файлом
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionRegfile()
    {
        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody());

        if (empty($kf->partner->SchetTcbNominal)) {
            return ['status' => 0, 'message' => 'Номинальный счет не найден'];
        }

        $KfBenific = new KfBenific();
        $data = $KfBenific->EncodeFile($kf->GetReq('data'));
        if (!$data) {
            return ['status' => 0, 'message' => 'Неверный формат файла'];
        }

        $TcbGate = new TcbGate($kf->IdPartner, TCBank::$AFTGATE);
        $tcBank = new TCBank($TcbGate);
        $ret = $tcBank->RegisterBenificiar([
            'req' => $data
        ]);

        if (isset($ret['status']) && $ret['status'] == 1) {
            $KfBenific = new KfBenific();
            $KfBenific->setAttributes(['result' => $ret['soap']]);
            $ret = $KfBenific->ParseResult();
            return ['status' => $ret['error'] == 0 ? 1 : 0, 'message' => $ret['message']];
        }

        return ['status' => 0, 'message' => 'Ошибка запроса'];

    }
}