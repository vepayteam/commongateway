<?php

namespace app\modules\partner\controllers;

use app\models\mfo\MfoBalance;
use app\models\partner\PartUserAccess;
use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\services\balance\BalanceService;
use app\services\balance\models\PartsBalanceForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MfoController extends Controller
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

    /**
     * Балансы МФО
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionBalance()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $sel = $this->selectPartner($idpartner, false, true);
            if (empty($sel)) {

                $partner = Partner::findOne(['ID' => $idpartner]);
                $MfoBalance = new MfoBalance($partner);

                return $this->render('balance', [
                    'IsAdmin' => 1,
                    'Partner' => $partner,
                    'balances' => $MfoBalance->GetBalance(true)
                ]);
            } else {
                return $sel;
            }
        } else {

            $idpartner = UserLk::getPartnerId(Yii::$app->user);
            $partner = Partner::findOne(['ID' => $idpartner]);

            $MfoBalance = new MfoBalance($partner);
            return $this->render('balance', [
                'IsAdmin' => 0,
                'Partner' => $partner,
                'balances' => $MfoBalance->GetBalance(false)
            ]);
        }
    }

    /**
     * Балансы МФО
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionPartsBalance()
    {
        $partners = null;
        $data = null;
        $partners = ArrayHelper::index(
            Partner::find()->select(['ID', 'Name'])->where(['IsBlocked' => 0, 'IsDeleted' => 0])->all(), 'ID'
        );

        if(Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            if ($IsAdmin) {
                $post['partnerId'] = (int)Yii::$app->request->post('partnerId');
            } else {
                $post['partnerId'] = UserLk::getPartnerId(Yii::$app->user);
            }

            $partsBalanceForm = new PartsBalanceForm();
            if($partsBalanceForm->load($post, '') || !$partsBalanceForm->validate()) {
                $data = $this->getBalanceService()->getPartsBalance($partsBalanceForm);
            }
        }

        return $this->render('parts_balance', compact('partners', 'data'));
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $sel = $this->selectPartner($idpartner, false, true);
            if (empty($sel)) {

                $partner = Partner::findOne(['ID' => $idpartner]);
                $MfoBalance = new MfoBalance($partner);

                return $this->render('parts_balance');
            } else {
                return $sel;
            }
        } else {

            $idpartner = UserLk::getPartnerId(Yii::$app->user);
            $partner = Partner::findOne(['ID' => $idpartner]);

            $MfoBalance = new MfoBalance($partner);
            return $this->render('parts_balance');
        }
    }

    /**
     * Выписка по счету
     *
     * @return array|Response
     * @throws \yii\db\Exception
     */
    public function actionBalanceorder()
    {
        if (Yii::$app->request->isAjax) {
            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            if ($IsAdmin) {
                $idpartner = (int)Yii::$app->request->post('idpartner');

            } else {
                $idpartner = UserLk::getPartnerId(Yii::$app->user);
            }

            $dateFrom = strtotime(Yii::$app->request->post('datefrom') . ":00");
            $dateTo = strtotime(Yii::$app->request->post('dateto') . ":59");
            $istransit = (int)Yii::$app->request->post('istransit', 0);
            $sort = (int)Yii::$app->request->post('sort', 0);

            Yii::$app->response->format = Response::FORMAT_JSON;

            $partner = Partner::findOne(['ID' => $idpartner]);
            $MfoBalance = new MfoBalance($partner);

            if ($istransit == 10 || $istransit == 11) {
                return [
                    'status' => 1,
                    'ostbeg' => number_format($MfoBalance->GetOstBeg($dateFrom, $dateTo,$istransit - 10)/100.0, 2, '.', ' '),
                    'ostend' => number_format($MfoBalance->GetOstEnd($dateTo, $istransit - 10)/100.0, 2, '.', ' '),
                    'data' => $this->renderPartial('_balanceorderlocal', [
                        'listorder' => $MfoBalance->GetOrdersLocal($istransit - 10, $dateFrom, $dateTo, $sort),
                        'IsAdmin' => $IsAdmin,
                        'sort' => $sort
                    ])
                ];
            } else {
                return [
                    'status' => 1,
                    'ostbeg' => number_format($MfoBalance->GetOstBeg($dateFrom, $dateTo, $istransit)/100.0, 2, '.', ' '),
                    'ostend' => number_format($MfoBalance->GetOstEnd($dateTo, $istransit)/100.0, 2, '.', ' '),
                    'data' => $this->renderPartial('_balanceorder', [
                        'listorder' => $MfoBalance->GetBankStatemets($istransit, $dateFrom, $dateTo, $sort),
                        'IsAdmin' => $IsAdmin,
                        'sort' => $sort,
                        'dateFrom' => $dateFrom,
                        'dateTo' => $dateTo,
                        'istransit' => $istransit,
                        'IdPartner' => $idpartner
                    ])
                ];
            }
        } else {
            return $this->redirect('/partner');
        }
    }

    /**
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionExportvyp()
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        if ($IsAdmin) {
            $idpartner = (int)Yii::$app->request->get('idpartner');
        } else {
            $idpartner = UserLk::getPartnerId(Yii::$app->user);
        }
        $partner = Partner::findOne(['ID' => $idpartner]);
        $MfoBalance = new MfoBalance($partner);
        $data = $MfoBalance->ExportVyp(Yii::$app->request->get());
        if ($data) {
            Yii::$app->response->format = Response::FORMAT_RAW;
            Yii::$app->response->setDownloadHeaders(
                "export_vyp.xlsx",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            );

            return $data;
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @return BalanceService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getBalanceService()
    {
        return Yii::$container->get('BalanceService');
    }
}
