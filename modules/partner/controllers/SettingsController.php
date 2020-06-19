<?php

namespace app\modules\partner\controllers;

use app\models\bank\Banks;
use app\models\mfo\MfoDistributionReports;
use app\models\mfo\MfoSettings;
use app\models\Options;
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

            $settings = [];
            for ($i = 0; $i < 3; $i++) {
                $settings[$i] = AlarmsSettings::findOne(['TypeAlarm' => $i]);
                if (!$settings[$i]) {
                    $settings[$i] = new AlarmsSettings();
                }
            }

            $opt = Options::findOne(['Name' => 'disabledday']);

            $banks = Banks::find()->where(['>', 'ID', '1'])->all();

            return $this->render('alarms', [
                'settings' => $settings,
                'IsAdmin' => $IsAdmin,
                'veekends' => $opt ? $opt->Value : '',
                'banks' => $banks
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

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionSaveveekenddays()
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);

        if ($IsAdmin && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $opt = Options::findOne(['Name' => 'disabledday']);
            if (!$opt) {
                $opt = new Options();
                $opt->Name = 'disabledday';
            }
            $opt->Value = Yii::$app->request->post('veekenddays', '');
            if ($opt->save(false)) {
                return ['status' => 1, 'message' => 'Данные внесены'];
            } else {
                return ['status' => 0, 'message' => 'Ошибка сохранения'];
            }

        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionSavebankconf()
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);

        if ($IsAdmin && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $option = Options::findOne(['Name' => 'bank_payment_id']);
            $option->Value = Yii::$app->request->post('bank_payment_id');
            $option->save();

            $inBanks = Yii::$app->request->post('Bank', '');
            foreach ($inBanks as $bank) {
                $banksave = Banks::findOne(['ID' => $bank['ID']]);
                if (!$banksave) {
                    return ['status' => 0, 'message' => 'Ошибка сохранения'];
                }
                $banksave->SortOrder = $bank['SortOrder'];
                $banksave->UsePayIn = $bank['UsePayIn'] ?? 0;
                $banksave->UseApplePay = $bank['UseApplePay'] ?? 0;
                $banksave->UseGooglePay = $bank['UseGooglePay'] ?? 0;
                $banksave->UseSamsungPay = $bank['UseSamsungPay'] ?? 0;
                if (!$banksave->save()) {
                    return ['status' => 0, 'message' => 'Ошибка сохранения'];
                }
            }
            return ['status' => 1, 'message' => 'Данные сохранены'];

        } else {
            throw new NotFoundHttpException();
        }
    }

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
