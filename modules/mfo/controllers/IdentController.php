<?php

namespace app\modules\mfo\controllers;

use app\models\api\CorsTrait;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\mfo\IdentificationUser;
use app\models\mfo\MfoReq;
use app\services\ident\exceptions\RunaIdentException;
use app\services\ident\forms\RunaIdentInitForm;
use app\services\ident\forms\RunaIdentStateForm;
use app\services\ident\IdentService;
use app\services\ident\models\IdentRuna;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
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
            'runa-init' => ['POST'],
            'runa-state' => ['POST'],
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
            $TcbGate = new TcbGate($mfo->mfo, TCBank::$ECOMGATE);
            $tcBank = new TCBank($TcbGate);
            $ret = $tcBank->personGetIndentResult($id);
            return $ret;
        }

        return ['status' => 0];
    }

    public function actionRunaInit()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        Yii::$app->session->set('partnerId', $mfo->mfo);
        $runaIdentInitForm = new RunaIdentInitForm();
        $post = json_decode(Yii::$app->request->getRawBody(), true);
        $runaIdentInitForm->load($post, '');

        if(!$runaIdentInitForm->validate()) {
            return [
                'status' => 0,
                'message' => $runaIdentInitForm->getError(),
            ];
        }

        try {
            $runaIdentInitResponse = $this->getIdentService()->runaInit($runaIdentInitForm);
        }  catch (RunaIdentException | NotInstantiableException | InvalidConfigException $e) {
            return [
                'status' => 2,
                'message' => $e->getMessage(),
            ];
        }

        return [
            'status' => 1,
            'id' => $runaIdentInitResponse->identRuna->Id,
            'message' => 'Заявка создана',
        ];
    }

    public function actionRunaState()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        Yii::$app->session->set('partnerId', $mfo->mfo);
        $post = json_decode(Yii::$app->request->getRawBody(), true);
        $identRunaId = (isset($post['id']) ? (int)$post['id'] : 0);
        $identRuna = IdentRuna::findOne(['Id' => $identRunaId, 'PartnerId' => $mfo->mfo]);

        if(!$identRuna) {
            throw new BadRequestHttpException();
        }

        $runaIdentStateForm = new RunaIdentStateForm();
        $runaIdentStateForm->tid = $identRuna->Tid;
        $runaIdentStateForm->attach_smev_response = true;

        try {
            $runaIdentStateResponse = $this->getIdentService()->runaState($runaIdentStateForm);
        } catch (RunaIdentException | NotInstantiableException | InvalidConfigException $e) {
            return [
                'status' => 2,
                'message' => $e->getMessage(),
            ];
        }

        return [
            'status' => $runaIdentStateResponse->details['code'] == "0" ? 1 : 2,
            'message' => $runaIdentStateResponse->details['description'],
            'details' => $runaIdentStateResponse->details,
        ];
    }

    /**
     * @return IdentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getIdentService()
    {
        return Yii::$container->get('IdentService');
    }
}
