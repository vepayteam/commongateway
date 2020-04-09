<?php

namespace app\modules\partner\controllers;

use app\models\mfo\MfoDistributionReports;
use app\models\mfo\MfoSettings;
use app\models\partner\admin\AlarmsSettings;
use app\models\partner\PartUserAccess;
use app\models\partner\stat\StatFilter;
use app\models\partner\UserLk;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SettingsController extends Controller
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
                            return !(UserLk::IsAdmin(Yii::$app->user) == true ||
                                PartUserAccess::checkRazdelAccess(Yii::$app->user, $action) == true ||
                                UserLk::IsMfo(Yii::$app->user));
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
            'index' => ['GET', 'POST'],
        ];
    }

    public function actionIndex()
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        if ($IsAdmin) {

            /*$IdPart = Yii::$app->request->post('IdPartner', 0);
            $sel = $this->selectPartner($IdPart, false, false, true);
            if (empty($sel)) {
                $MfoSettings = new MfoSettings(['IdPartner' => $IdPart]);
                $MfoSettings->ReadUrl();
                return $this->render('callback', [
                    'settings' => $MfoSettings,
                    'IdPartner' => $IdPart,
                    'IsAdmin' => $IsAdmin
                ]);
            } else {
                return $this->render('index', [
                    'sel' => $sel,
                    'IsAdmin' => $IsAdmin
                ]);
            }*/
            $settings = [];
            for ($i = 0; $i < 3; $i++) {
                $settings[$i] = AlarmsSettings::findOne(['TypeAlarm' => $i]);
                if (!$settings[$i]) {
                    $settings[$i] = new AlarmsSettings();
                }
            }
            return $this->render('alarms', [
                'settings' => $settings,
                'IsAdmin' => $IsAdmin
            ]);

        } else {
            $idpartner = UserLk::getPartnerId(Yii::$app->user);

            $MfoSettings = new MfoSettings(['IdPartner' => $idpartner]);
            $MfoSettings->ReadUrl();
            return $this->render('callback', [
                'settings' => $MfoSettings,
                'IdPartner' => $idpartner,
                'IsAdmin' => $IsAdmin
            ]);
        }
    }

    /*public function actionDistribution()
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        if ($IsAdmin) {
            $fltr = new StatFilter();
            $partners = $fltr->getPartnersList(false);

            return $this->render('distribution', [
                'partners' => $partners,
                'IsAdmin' => $IsAdmin
            ]);
        }
        return $this->redirect('/partner');

    }*/

    public function actionAlarms()
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        if ($IsAdmin) {
            $settings = [];
            for ($i = 0; $i < 3; $i++) {
                $settings[$i] = AlarmsSettings::findOne(['TypeAlarm' => $i]);
                if (!$settings[$i]) {
                    $settings[$i] = new AlarmsSettings();
                }
            }
            return $this->render('alarms', [
                'settings' => $settings,
                'IsAdmin' => $IsAdmin
            ]);
        }
        return $this->redirect('/partner');
    }

    /**
     * Рассылка реестров (Ajax - Сохранить)
     */
    /*public function actionSaveDistribution()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        if ($IsAdmin) {
            $model = new MfoDistributionReports();
            if ($model->validate()) {
                $model->save();
                return [
                    'status' => 1,
                    'message' => 'Данные успешно сохранены, только для партенеров, у которых указан email.'];
            } else {
                return [
                    'status' => 0,
                    'data' => $model->errors,
                    'message' => 'Ошибка, неверно заполненны данные.'];
            }
        }
        return ['status' => 0, 'message' => 'У вас нет доступа к этому разделу сайта.'];
    }*/

    /**
     * @return array|Response
     */
    public function actionSettingssave()
    {
        if (Yii::$app->request->isAjax) {
            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            $idpartner = $IsAdmin ? Yii::$app->request->post('IdPartner') : UserLk::getPartnerId(Yii::$app->user);

            Yii::$app->response->format = Response::FORMAT_JSON;

            $MfoSettings = new MfoSettings(['IdPartner' => $idpartner]);
            if ($MfoSettings->Load(Yii::$app->request->post(), 'Settings') && $MfoSettings->validate()) {
                $MfoSettings->Save();

                return ['status' => 1];
            }
            return ['status' => 0, 'message' => 'Ошибка, указан неверный URL-адрес'];
        }
        return $this->redirect('/partner');
    }

    public function actionAlarmssave()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if (AlarmsSettings::SaveAll(Yii::$app->request->post(), $error)) {
                return ['status' => 1, 'message' => 'OK'];
            }

            return ['status' => 0, 'message' => 'Ошибка:' . $error];
        }
        return $this->redirect('/partner');
    }

}