<?php

namespace app\modules\partner\controllers;

use app\models\partner\order\OrderStat;
use app\models\partner\PartnerUsers;
use app\models\partner\PartUserAccess;
use app\models\partner\stat\StatFilter;
use app\models\partner\UserLk;
use app\models\payonline\OrderNotif;
use app\models\payonline\OrderPay;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

class OrderController extends Controller
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
                            return !(UserLk::IsAdmin(Yii::$app->user) == true || UserLk::IsMfo(Yii::$app->user) == true ||
                                (PartUserAccess::checkRazdelAccess(Yii::$app->user, $action) == true && UserLk::IsMfo(Yii::$app->user) == false)
                            );
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
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        $fltr = new StatFilter();
        return $this->render('index', [
            'IsAdmin' => $IsAdmin,
            'partnerlist' => $IsAdmin ? $fltr->getPartnersList() : null
        ]);
    }

    public function actionList()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $OrderStat = new OrderStat();
            $OrderStat->load(Yii::$app->request->post(), '');
            if ($OrderStat->validate()) {
                $list = $OrderStat->GetList();
                return ['status' => 1, 'data' => $this->renderPartial('_list', ['data' => $list])];
            } else {
                return ['status' => 0, 'message' => $OrderStat->GetError()];
            }
        }
        return $this->redirect('/partner');
    }

    public function actionAdd()
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        $IdPart = UserLk::getPartnerId(Yii::$app->user);
        if ($IsAdmin) {
            $sel = $this->selectPartner($IdPart);
            if (empty($sel)) {
                $order = new OrderPay();
                $order->IdPartner = $IdPart;
                return $this->render('add', [
                    'IsAdmin' => $IsAdmin,
                    'order' => $order
                ]);
            } else {
                return $sel;
            }

        } else {
            $order = new OrderPay();
            $order->IdPartner = UserLk::getPartnerId(Yii::$app->user);
            return $this->render('add', [
                'IsAdmin' => $IsAdmin,
                'order' => $order
            ]);
        }
    }

    public function actionSave()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $orderto = Yii::$app->request->post();

            $OrderTo = $orderto['OrderPay']['OrderTo'] ?? [];
            $basket = null;
            foreach($OrderTo as $key => $value) {
                foreach($value as $k => $val) {
                    $basket[$k][$key] = $val;
                }
            }

            $order = new OrderPay();
            $order->load($orderto, 'OrderPay');

            if($basket) {
                $order->OrderTo = json_encode($basket);
            }

            if ($order->validate()) {
                $order->save(false);

                //отправка ссылки для оплаты
                $notif = new OrderNotif();
                $notif->SendNotif($order);

                return ['status' => 1, 'message' => 'Счет создан'];
            } else {
                return ['status' => 0, 'message' => $order->GetError()];
            }
        }
        return $this->redirect('/partner');
    }

    public function actionCancel()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $order = new OrderStat();
            return $order->CancelOrder(Yii::$app->request->post('id'));
        }
        return $this->redirect('/partner');
    }

    public function actionResend()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $order = new OrderStat();
            return $order->Resend(Yii::$app->request->post('id'));
        }
        return $this->redirect('/partner');
    }
}