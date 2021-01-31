<?php

namespace app\modules\partner\controllers;

use app\models\mfo\MfoBalance;
use app\models\partner\callback\CallbackList;
use app\models\partner\PartUserAccess;
use app\models\partner\UserLk;
use app\services\notifications\jobs\CallbackSendJob;
use app\services\notifications\models\NotificationPay;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

class CallbackController extends Controller
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
            'list' => ['GET', 'POST'],
        ];
    }

    /**
     * Список колбэков МФО
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionList()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $sel = $this->selectPartner($idpartner, false, false);
            if (empty($sel)) {
                return $this->render('list', ['idpartner' => $idpartner]);
            } else {
                return $sel;
            }
        } else {

            $idpartner = UserLk::getPartnerId(Yii::$app->user);

            return $this->render('list', ['idpartner' => $idpartner]);
        }
    }

    public function actionListitems()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);

            $CallbackList = new CallbackList();
            if ($CallbackList->load(Yii::$app->request->post(), '') && $CallbackList->validate()) {
                $data = $CallbackList->GetList($IsAdmin);
                return ['status' => 1, 'data' => $this->renderPartial('_listitems', ['data' => $data, 'IsAdmin' => $IsAdmin])];
            } else {
                return ['status' => 0, 'message' => $CallbackList->GetError()];
            }

        } else {
            return $this->redirect('/partner');
        }

    }

    public function actionRepeat()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            $notificationPayId = Yii::$app->request->post('id', null);
            $notificationPay = NotificationPay::findOne(['ID' => $notificationPayId]);

            if(!$notificationPay || !$IsAdmin && $notificationPay->paySchet->IdOrg != Yii::$app->user->id) {
                return ['status' => 0, 'message' => 'Ошибка запроса повтора операции'];
            } else {
                $notificationPay->HttpCode = 0;
                $notificationPay->DateLastReq = 0;
                $notificationPay->DateSend = 0;
                $notificationPay->HttpAns = null;
                $notificationPay->save(false);

                \Yii::$app->queue->push(new CallbackSendJob([
                    'notificationPayId' => $notificationPayId,
                ]));
                return ['status' => 1, 'message' => 'Запрос колбэка возвращен в очередь'];
            }
        } else {
            return $this->redirect('/partner');
        }
    }
}
