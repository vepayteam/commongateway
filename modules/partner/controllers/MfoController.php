<?php

namespace app\modules\partner\controllers;

use app\helpers\ExcelHelper;
use app\models\mfo\MfoBalance;
use app\models\partner\PartUserAccess;
use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\modules\partner\models\forms\PartListForm;
use app\modules\partner\models\search\PartListFilter;
use app\modules\partner\services\PartService;
use app\services\balance\Balance;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Response;

class MfoController extends Controller
{
    use SelectPartnerTrait;

    public $enableCsrfValidation = false;
    /**
     * @var PartService
     */
    private $partService;

    /**
     * {@inheritDoc}
     */
    public function behaviors(): array
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
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->partService = Yii::$app->get(PartService::class);
    }

    /**
     * Балансы МФО
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionBalance()
    {
        $isAdmin = UserLk::IsAdmin(Yii::$app->user);
        if ($isAdmin) {
            $sel = $this->selectPartner($idpartner, false, true);
            if (!empty($sel)) {
                return $sel;
            }
        } else {
            $idpartner = UserLk::getPartnerId(Yii::$app->user);
        }

        $partner = Partner::findOne(['ID' => $idpartner]);
        $balance = new Balance();
        $balance->setAttributes([
            'partner' => $partner
        ]);

        return $this->render('balance', [
            'IsAdmin' => $isAdmin,
            'Partner' => $partner,
            'BalanceResponse' => $balance->getAllBanksBalance(),
        ]);
    }

    /**
     * @param array $columns
     * @return string|Response
     * @throws RangeNotSatisfiableHttpException
     */
    private function partsInternal(
        array $columns = [
            'paySchetId',
            'partnerName',
            'partAmount',
            'createdAt',
            'extId',
            'paySchetAmount',
            'clientCompensation',
            'partnerCompensation',
            'bankCompensation',
            'message',
            'cardNumber',
            'cardHolder',
            'contract',
            'fio',
            'withdrawalPayschetId',
            'withdrawalAmount',
            'withdrawalCreatedAt',
        ]
    )
    {
        $model = new PartListForm();
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $model->scenario = PartListForm::SCENARIO_ADMIN;

            $partners = Partner::find()
                ->andWhere(['IsBlocked' => 0, 'IsDeleted' => 0])
                ->all();
            $partnerList = ArrayHelper::map($partners, 'ID', function (Partner $partner) {
                return "{$partner->ID} | {$partner->Name}";
            });
        } else {
            $model->scenario = PartListForm::SCENARIO_PARTNER;
            $model->partnerId = UserLk::getPartnerId(Yii::$app->user);

            $partnerList = null;
        }

        $searchModel = null;
        $dataProvider = null;

        if ($model->load(\Yii::$app->request->queryParams) && $model->validate()) {
            $searchModel = new PartListFilter();
            $searchModel->load(\Yii::$app->request->queryParams);

            // return Excel file
            if (Yii::$app->request->get('excel')) {
                $dataProvider = $this->partService->search($model, $searchModel, true);
                $htmlString = $this->renderPartial('parts/grid', [
                    'searchModel' => null,
                    'dataProvider' => $dataProvider,
                    'columns' => $columns,
                ]);
                return Yii::$app->response->sendContentAsFile(
                    ExcelHelper::generateFromHtml($htmlString),
                    "export_parts_balance_{$model->partnerId}.xlsx",
                    ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
                );
            }

            $dataProvider = $this->partService->search($model, $searchModel);
        }

        return $this->render('parts', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'columns' => $columns,
            'partnerList' => $partnerList,
        ]);
    }

    /**
     * Балансы МФО
     *
     * @return string|Response
     * @throws \yii\db\Exception
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionPartsBalance()
    {
        if (!UserLk::IsAdmin(Yii::$app->user) && !PartUserAccess::checkPartsBalanceAccess()) {
            return $this->redirect('/partner');
        }

        return $this->partsInternal();
    }

    /**
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionPartsBalancePartner()
    {
        if (!UserLk::IsAdmin(Yii::$app->user) && !PartUserAccess::checkPartsBalanceAccess()) {
            return $this->redirect('/partner');
        }

        return $this->partsInternal([
            'paySchetId',
            'partnerName',
            'partAmount',
            'createdAt',
            'message',
            'contract',
            'fio',
            'withdrawalPayschetId',
            'withdrawalAmount',
            'withdrawalCreatedAt',
        ]);
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
                $istransit -= 10;
            }
            $ostBeg = number_format($MfoBalance->GetOstBeg($dateFrom, $dateTo, $istransit) / 100.0, 2, '.', ' ');
            $ostEnd = number_format($MfoBalance->GetOstBeg($dateFrom, $dateTo, $istransit) / 100.0, 2, '.', ' ');
            $data = ($istransit == 10 || $istransit == 11)
                ? $this->renderPartial('_balanceorderlocal', [
                    'listorder' => $MfoBalance->GetOrdersLocal($istransit - 10, $dateFrom, $dateTo, $sort),
                    'IsAdmin' => $IsAdmin,
                    'sort' => $sort
                ])
                : $this->renderPartial('_balanceorder', [
                    'listorder' => $MfoBalance->GetBankStatemets($istransit, $dateFrom, $dateTo, $sort),
                    'IsAdmin' => $IsAdmin,
                    'sort' => $sort,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'istransit' => $istransit,
                    'IdPartner' => $idpartner
                ]);

            return [
                'status' => 1,
                'ostbeg' => $ostBeg,
                'ostend' => $ostEnd,
                'data' => $data
            ];
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
}
