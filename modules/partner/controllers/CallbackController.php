<?php

namespace app\modules\partner\controllers;

use app\models\partner\callback\CallbackFilter;
use app\models\partner\callback\CallbackList;
use app\models\partner\callback\CallbackStat;
use app\models\partner\PartUserAccess;
use app\models\partner\UserLk;
use app\models\queue\JobPriorityInterface;
use app\services\notifications\jobs\CallbackSendJob;
use app\services\notifications\models\NotificationPay;
use app\services\payment\models\PaySchet;
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
     * Список коллбэков МФО
     * @return string
     */
    public function actionList()
    {
        $fltr = new CallbackFilter();

        $isAdmin = UserLk::IsAdmin(Yii::$app->user);

        return $this->render('list', [
            'httpCodeList' => $fltr->getCallbackHTTPResponseStatusList(),
            'IsAdmin' => $isAdmin,
            'partnerlist' => $isAdmin ? $fltr->getPartnersList() : []
        ]);
    }

    public function actionListitems()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $reqData = Yii::$app->request->post();

            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            $page = (int) Yii::$app->request->get('page', 1);

            $CallbackList = new CallbackList();
            if ($CallbackList->load(Yii::$app->request->post(), '') && $CallbackList->validate()) {
                $data = $CallbackList->GetList($IsAdmin, $page);

                return ['status' => 1, 'data' => $this->renderPartial('_listitems', [
                    'reqdata' => $reqData,
                    'data' => $data['data'],
                    'payLoad' => $data['payLoad']->toArray(),
                    'IsAdmin' => $IsAdmin
                ])];
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

            if(!$notificationPay || !$IsAdmin && $notificationPay->paySchet->IdOrg != Yii::$app->user->getIdentity()->getPartner()) {
                return ['status' => 0, 'message' => 'Ошибка запроса повтора операции'];
            } else {
                $this->repeatIteration($notificationPay);
                return ['status' => 1, 'message' => 'Запрос колбэка возвращен в очередь'];
            }
        } else {
            return $this->redirect('/partner');
        }
    }

    public function actionRepeatbatch()
    {
        if (Yii::$app->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);

            $CallbackList = new CallbackList();

            if ($CallbackList->load(Yii::$app->request->post(), '') && $CallbackList->validate()) {

                $data = $CallbackList->GetList($IsAdmin, 0, true);
                $notificationPayIdList = array_column($data['data'], 'ID');

                if (count($notificationPayIdList) > 0) {

                    $notificationPayList = NotificationPay::findAll($notificationPayIdList);

                    foreach ($notificationPayList as $notificationPay) {
                        if (!$IsAdmin && $notificationPay->paySchet->IdOrg != Yii::$app->user->id) {
                            continue;
                        }
                        $this->repeatIteration($notificationPay);
                    }
                }

                return ['status' => 1, 'message' => 'Запросы колбэка возвращены в очередь'];
            }

            return ['status' => 0, 'message' => $CallbackList->GetError()];
        }

        return $this->redirect('/partner');
    }

    public function actionListexport()
    {
        ini_set('memory_limit', '1024M');
        $callbackStat = new CallbackStat();
        $callbackStat->ExportOpListRaw(Yii::$app->request->get());
    }

    private function repeatIteration(NotificationPay $notificationPay): void
    {
        if (in_array($notificationPay->paySchet->Status, [PaySchet::STATUS_DONE, PaySchet::STATUS_ERROR])) {
            $notificationPay->HttpCode = 0;
            $notificationPay->DateLastReq = 0;
            $notificationPay->DateSend = 0;
            $notificationPay->HttpAns = null;
            $notificationPay->save(false);

            \Yii::$app->queue->push(new CallbackSendJob([
                'notificationPayId' => $notificationPay->ID,
            ]));
        }
    }
}
