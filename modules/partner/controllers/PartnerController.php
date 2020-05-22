<?php

namespace app\modules\partner\controllers;

use app\models\mfo\DistributionReports;
use app\models\mfo\MfoSettings;
use app\models\partner\admin\Partners;
use app\models\partner\admin\SmsConfigForm;
use app\models\partner\admin\Uslugi;
use app\models\partner\PartnerUsers;
use app\models\partner\PartUserAccess;
use app\models\partner\stat\StatFilter;
use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\models\payonline\PartnerBankRekviz;
use app\models\payonline\Uslugatovar;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PartnerController extends Controller
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
                            return !(UserLk::IsAdmin(Yii::$app->user) == true/* ||
                                PartUserAccess::checkRazdelAccess(Yii::$app->user, $action) == true ||
                                UserLk::IsMfo(Yii::$app->user)*/);
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
        ];
    }

    public function actionIndex()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $partners = new Partners();
            $fltr = new StatFilter();
            return $this->render('list', [
                'list' => $partners->getPartnersList(),
                'roleUser' => UserLk::getUserRole(Yii::$app->user),
                'IsAdmin' => UserLk::IsAdmin(Yii::$app->user),
                'partnerlist' => $fltr->getPartnersList()
            ]);
        } else {
            return $this->render('index', [
                'users' => PartnerUsers::getList(UserLk::getPartnerId(Yii::$app->user)),
                'roleUser' => UserLk::getUserRole(Yii::$app->user),
                'IsAdmin' => UserLk::IsAdmin(Yii::$app->user),
                'partner' => UserLk::getPart(Yii::$app->user)
            ]);
        }
    }

    public function actionPartnerAdd()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {

                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

                $partner = new Partner();
                $partner->load(Yii::$app->request->post(), 'PartnerAdd');
                if (!$partner->validate()) {
                    return ['status' => 0, 'message' => $partner->GetError()];
                }
                $partner->save(false);

                if ($partner->IsMfo) {
                    //создание услуг МФО при добавлении
                    $partner->CreateUslugMfo();
                } else {
                    //создание услуги магазину при добавлении
                    $partner->CreateUslug();
                }

                return ['status' => 1, 'id' => $partner->ID];

            }
        }
        return $this->redirect('/partner');
    }

    /**
     * Редактирование контрагнета
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionPartnerEdit($id)
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {

            $partner = Partner::findOne(['ID' => $id, 'IsDeleted' => 0]);
            if (!$partner) {
                throw new NotFoundHttpException();
            }

            $usl = new Uslugi();

            $MfoSettings = new MfoSettings(['IdPartner' => $partner->ID]);
            $MfoSettings->ReadUrl();

            $PartnerAdmin = PartnerUsers::findOne(['IsAdmin' => 0, 'RoleUser' => 1, 'IdPartner' => $partner->ID, 'IsDeleted' => 0]);
            if (!$PartnerAdmin) {
                $PartnerAdmin = new PartnerUsers();
                $PartnerAdmin->IdPartner = $partner->ID;
                $PartnerAdmin->IsAdmin = 0;
                $PartnerAdmin->RoleUser = 1;
                $PartnerAdmin->IsActive = 1;
            }

            return $this->render('index', [
                //'users' => PartnerUsers::getList($id),
                //'roleUser' => UserLk::getUserRole(Yii::$app->user),
                'IsAdmin' => UserLk::IsAdmin(Yii::$app->user),
                'partner' => $partner,
                'uslugi' => $usl->getList($id),
                'settings' => $MfoSettings,
                'PartnerAdmin' => $PartnerAdmin
            ]);
        }
        return $this->redirect('/partner');
    }

    /**
     * Сохранить данные контрагента
     * @return array|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionPartnerSave()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {

                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

                $partner = Partner::findOne(['ID' => Yii::$app->request->post('Partner_ID'), 'IsDeleted' => 0]);
                if (!$partner) {
                    return ['status' => 0, 'message' => 'Контрагент не найден'];
                }

                $partner->load(Yii::$app->request->post(), 'Partner');
                if (!$partner->validate()) {
                    return ['status' => 0, 'message' => $partner->GetError()];
                }
                $partner->save(false);

                return ['status' => 1, 'id' => $partner->ID];

            }
        }
        return $this->redirect('/partner');
    }

    /**
     * Редактирование услуги
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUslugiEdit($id)
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {

            $usl = Uslugatovar::findOne(['ID' => $id, 'IsDeleted' => 0]);
            if (!$usl) {
                throw new NotFoundHttpException();
            }
            $partner = Partner::findOne(['ID' => $usl->IDPartner]);
            return $this->render('uslugi_edit', ['usl' => $usl, 'partner' => $partner]);
        }
        return $this->redirect('/partner');
    }

    /**
     * Добавление услуги
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUslugiAdd($id)
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {

            $partner = Partner::findOne(['ID' => $id, 'IsDeleted' => 0]);
            if (!$partner) {
                throw new NotFoundHttpException();
            }
            $usl = new Uslugatovar();
            $usl->IDPartner = $id;
            return $this->render('uslugi_edit', ['usl' => $usl, 'partner' => $partner]);
        }
        return $this->redirect('/partner');
    }

    /**
     * Сохранить услугу
     * @return array|\yii\web\Response
     */
    public function actionUslugiSave()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {

                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

                $usl = Uslugatovar::findOne(['ID' => Yii::$app->request->post('ID')]);
                if (!$usl) {
                    $usl = new Uslugatovar();
                    $usl->IDPartner = Yii::$app->request->post('IdPartner');
                }
                $usl->load(Yii::$app->request->post(), 'Uslugatovar');
                if (!$usl->validate()) {
                    return ['status' => 0, 'message' => array_values($usl->getFirstErrors())];
                }
                $usl->save(false);

                return ['status' => 1, 'id' => $usl->ID];
            }
        }
        return $this->redirect('/partner');
    }

    /**
     * Удалить услугу
     * @return array|\yii\web\Response
     */
    public function actionUslugiDel()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {

                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

                $usl = Uslugatovar::findOne(['ID' => (int)Yii::$app->request->post('ID')]);
                if ($usl) {
                    $usl->IsDeleted = 1;
                    $usl->save(false);
                    return ['status' => 1, 'id' => $usl->IDPartner];
                } else {
                    return ['status' => 0, 'message' => 'Услуга не найдена'];
                }
            }
        }
        return $this->redirect('/partner');
    }

    /**
     * Сохранить реквизиты для перечислений
     * @return array|string
     */
    public function actionRekvizSave()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {

                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

                //$bankrecv = PartnerBankRekviz::findOne(['ID' => Yii::$app->request->post('IdRecviz')]); 1-н к 1-му с Partner
                $bankrecv = PartnerBankRekviz::findOne(['IdPartner' => Yii::$app->request->post('PartnerBankRekviz')['IdPartner']]);
                if (!$bankrecv) {
                    $bankrecv = new PartnerBankRekviz();
                    $bankrecv->IdPartner = Yii::$app->request->post('PartnerBankRekviz')['IdPartner'];
                } else {
                    //удалить дубли..
                    $dl = PartnerBankRekviz::findAll([
                        'IdPartner' => Yii::$app->request->post('PartnerBankRekviz')['IdPartner'],
                    ]);
                    foreach ($dl as $d) {
                        if ($d->ID != $bankrecv->ID) {
                            $d->delete();
                        }
                    }
                }

                $bankrecv->load(Yii::$app->request->post(), 'PartnerBankRekviz');
                if (!$bankrecv->validate()) {
                    return ['status' => 0, 'message' => $bankrecv->GetError()];
                }
                $bankrecv->save(false);

                return ['status' => 1, 'id' => $bankrecv->ID];

            }
        }
        return '';
    }

    public function actionContSave()
    {
        if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $partner = new Partners();
            $partner->updateContPartner(Yii::$app->request);

            if ($partner->loadError) {
                return ['status' => 0, 'error' => $partner->loadErrorMesg];
            }
            return ['status' => 1];
        }
        return '';
    }

    public function actionMainsmsSave()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $model = SmsConfigForm::buildAjax(Yii::$app->request);
            return $model->answer();
        }
        return '';
    }

    /**
     * Рассылка реестров (Ajax - Сохранить)
     */
    public function actionSaveDistribution()
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        if ($IsAdmin && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $disRep = DistributionReports::findOne([
                'id' => Yii::$app->request->post('distribution_id'),
                'partner_id' => Yii::$app->request->post('partner_id')
            ]);

            if (!empty(Yii::$app->request->post('email'))) {
                if (!$disRep) {
                    $disRep = new DistributionReports();
                    $disRep->partner_id = Yii::$app->request->post('partner_id');
                    $disRep->last_send = 0;
                }
                $disRep->load(Yii::$app->request->post(), '');
                if ($disRep->validate()) {
                    $disRep->save(false);
                    return [
                        'status' => 1,
                        'message' => 'Рассылка сохранена'
                    ];
                }
            } else {
                if ($disRep) {
                    $disRep->delete();
                }
                return [
                    'status' => 1,
                    'message' => 'Рассылка удалена'
                ];
            }

            return [
                'status' => 0,
                'message' => 'Ошибка, неверно заполненны данные.'
            ];

        }
        return $this->redirect('/partner');
    }

    /**
     * Список пользователей мерчанта
     *
     * @param int $id IdUser
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUsersEdit($id)
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $user = PartnerUsers::findOne(['ID' => $id, 'IsDeleted' => 0]);
        } else {
            $IdPart = UserLk::getPartnerId(Yii::$app->user);
            $user = PartnerUsers::findOne(['ID' => $id, 'IdPartner' => $IdPart, 'IsDeleted' => 0]);
        }
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $partner = Partner::findOne(['ID' => $user->IdPartner, 'IsDeleted' => 0]);
        if (!$partner) {
            throw new NotFoundHttpException();
        }

        return $this->render('users-edit', [
            'user' => $user,
            'partner' => $partner,
            'IsAdmin' => UserLk::IsAdmin(Yii::$app->user)
        ]);
    }

    /**
     * Добавить пользователя
     * @param int $id IdPartner
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUsersAdd($id)
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $IdPart = Partner::findOne(['ID' => $id, 'IsDeleted' => 0])->ID;
        } else {
            $IdPart = UserLk::getPartnerId(Yii::$app->user);
        }

        $partner = Partner::findOne(['ID' => $IdPart, 'IsDeleted' => 0]);
        if (!$partner) {
            throw new NotFoundHttpException();
        }

        $user = new PartnerUsers();
        $user->IsActive = 1;
        $user->IdPartner = $partner->ID;
        return $this->render('users-edit', [
            'user' => $user,
            'partner' => $partner,
            'IsAdmin' => UserLk::IsAdmin(Yii::$app->user)
        ]);
    }

    /**
     * Сохранить пользователя
     * @return array|string
     */
    public function actionUsersSave()
    {
        if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            $us = PartnerUsers::findOne(['ID' => Yii::$app->request->post('ID')]);
            if (!$us) {
                $us = new PartnerUsers();
                $partner = Partner::getPartner(Yii::$app->request->post('IdPartner', 0));
                if ($partner) {
                    $us->IdPartner = $partner->ID;
                }
            }
            $us->load(Yii::$app->request->post(), 'PartnerUsers');
            if (!$us->validate()) {
                return ['status' => 0, 'message' => array_values($us->getFirstErrors())];
            }
            $us->save(false);

            PartUserAccess::saveRazdels($us, Yii::$app->request->post('razdely'));

            return ['status' => 1, 'id' => $us->ID];
        }
        return '';
    }

}