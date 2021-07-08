<?php

namespace app\modules\partner\controllers;

use app\models\partner\admin\Uslugi;
use app\models\partner\PartUserAccess;
use app\models\partner\stat\StatFilter;
use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\PartnerService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;

class UslugController extends Controller
{
    use SelectPartnerTrait;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->getResponse()->redirect(Url::toRoute('/partner'), 302)->send();
                            return false;
                        },
                        'matchCallback' => function ($rule, $action) {
                            return (!UserLk::IsAdmin(Yii::$app->user) && PartUserAccess::checkRazdelAccess(Yii::$app->user, $action) == false) ||
                                UserLk::IsMfo(Yii::$app->user);
                        }
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->getResponse()->redirect(Url::toRoute('/partner'), 302)->send();
                            return false;
                        }
                    ],
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ],

        ];
    }

    protected function verbs()
    {
        return [
            'add' => ['GET', 'POST'],
            'addpost' => ['POST'],
        ];
    }

    public function actionIndex()
    {
        $usl = new Uslugi();
        $fltr = new StatFilter();

        $isAdmin = UserLk::IsAdmin(\Yii::$app->user);
        $idpartner = UserLk::getPartnerId(\Yii::$app->user);

        return $this->render('index', [
            'partner' => Partner::findOne($idpartner),
            'uslug' => $usl->getPointsList($idpartner),
            'IsAdmin' => $isAdmin,
            'partnerlist' => $fltr->getPartnersList(),
            'magazlist' => $fltr->getMagazList($isAdmin ? 0 : $idpartner)
        ]);
    }

    public function actionAdd()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $IdUsl = Yii::$app->request->get('IdUsl', 0);
            $usl = Uslugatovar::find()
                ->select(['ID', 'NameUsluga', 'IDPartner', 'CustomData'])
                ->where([
                    'ID' => $IdUsl,
                    'IDPartner' => Yii::$app->request->get('IdPartner', 0),
                    'IsCustom' => 1,
                    'IsDeleted' => 0,
                ])->one();
            if ($usl) {
                return ['status' => 1, 'CustomData' => Json::decode($usl['CustomData'])];
            }
            return ['status' => 0];
        } else {
            $sel = $this->selectPartner($IdPart);
            if (empty($sel)) {
                $IdUsl = Yii::$app->request->get('IdUsl', 0);
                $usl = Uslugatovar::find()
                    ->select(['ID', 'NameUsluga', 'IDPartner', 'CustomData'])
                    ->where([
                        'ID' => $IdUsl,
                        'IDPartner' => $IdPart,
                        'IsCustom' => 1,
                        'IsDeleted' => 0,
                    ])->one();
                if (!$usl) {
                    $usl = new Uslugatovar();
                    $usl->IDPartner = $IdPart;
                    $usl->NameUsluga = 'Виджет';
                }
                return $this->render('add', [
                    'usl' => $usl
                ]);
            } else {
                return $sel;
            }
        }
    }

    public function actionAddpost()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $inp = Yii::$app->request->post();
            $IdPartner = Yii::$app->request->post('IdPartner', 0);
            $TypeTemplate = Yii::$app->request->post('TypeTemplate');
            $data = [
                "TypeTemplate" => empty($TypeTemplate) ? "two" : $TypeTemplate,
                "NameForm" => "Заказ", //Yii::$app->request->post('NameForm', "Заказ"),
                "fields1" => [],
                "fields2" => []
            ];
            if (isset($inp['inp1'])) {
                foreach ($inp['inp1'] as $item) {
                    $data['fields1'][] = Json::decode(urldecode($item));
                }
            }
            if (isset($inp['inp2'])) {
                foreach ($inp['inp2'] as $item) {
                    $data['fields2'][] = Json::decode(urldecode($item));
                }
            }

            if (isset($inp['valFile'])) {
                foreach ($inp['valFile'] as $uid => $item) {
                    $u = new Uslugi();
                    $u->uploadWidgetImg($item, $uid, $IdPartner);
                }
            }

            $IdUsl = Yii::$app->request->post('IdUsl', 0);
            $usl = Uslugatovar::findOne($IdUsl);
            if (!$usl) {
                $usl = new Uslugatovar();
                $usl->IDPartner = $IdPartner;
                $usl->IsCustom = 1;
                $usl->TypeExport = 1;
                $usl->PcComission = 0;
                $usl->ProvVoznagPC = 2.9;
                $usl->TypeReestr = 0;
                $usl->EmailReestr = '';
                $usl->IdBankRekviz = \Yii::$app->db
                    ->createCommand(
                        'SELECT `ID` FROM `partner_bank_rekviz` WHERE `IdPartner` = :PARTNER LIMIT 1', [
                        ':PARTNER' => $IdPartner
                    ])->queryScalar();
            }
            $usl->CustomData = Json::encode($data);
            $usl->NameUsluga = 'Виджет';//$data["NameForm"];
            if ($usl->save()) {
                return ['status' => 1, 'idusl' => $usl->ID];
            }
            return ['status' => 0];
        } else {
            return $this->redirect('/partner/uslug/index');
        }
    }



    public function actionPointEdit($id, PartnerService $service)
    {
        $usl = Uslugatovar::findOne(['ID' => $id]);
        $partner = $service->getPartner($usl->IDPartner);

        return $this->render('point-edit', [
            'usl' => $usl,
            'partner' => $partner,
            'mags' => $partner->getPartnerDogovor()->all(),
            'isAdmin' => UserLk::IsAdmin(\Yii::$app->user),
        ]);
    }

    public function actionPointAdd($id, PartnerService $service)
    {
        $sel = $id == 0 ? $this->selectPartner($id) : '';
        if (empty($sel)) {
            $usl = new Uslugatovar();
            $usl->IDPartner = $id;
            $partner = $service->getPartner($id);
            return $this->render('point-edit', [
                'usl' => $usl,
                'partner' => $partner,
                'mags' => $partner->getPartnerDogovor()->all(),
                'isAdmin' => UserLk::IsAdmin(\Yii::$app->user),
            ]);
        } else {
            return $sel;
        }
    }

    public function actionPointDel()
    {
        if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {

            $id = \Yii::$app->request->post('id');

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $usl = Uslugatovar::findOne(['ID' => $id, 'IsDeleted' => 0]);
            if ($usl) {
                if (!UserLk::IsAdmin(\Yii::$app->user) && $usl->IDPartner != UserLk::getPartnerId(\Yii::$app->user)) {
                    return ['status' => 0];
                }
                $usl->updateAttributes(['IsDeleted' => 1]);
                return ['status' => 1];
            } else {
                return ['status' => 0];
            }
        }
        return '';
    }

    public function actionPointSave()
    {
        if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $usl = new Uslugi();
            $usl->updateUsluga(Yii::$app->request);

            if ($usl->loadError) {
                return ['status' => 0, 'error' => $usl->loadErrorMesg];
            }
            return ['status' => 1, 'id' => $usl->loadID];
        }
        return '';
    }

}
