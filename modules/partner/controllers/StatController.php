<?php

namespace app\modules\partner\controllers;

use app\models\kkt\OnlineKassa;
use app\models\mfo\MfoStat;
use app\models\partner\admin\VyvodVoznag;
use app\models\partner\PartUserAccess;
use app\models\partner\stat\ActMfo;
use app\models\partner\stat\ActSchet;
use app\models\partner\stat\AutopayStat;
use app\models\partner\stat\Excerpt;
use app\models\partner\stat\export\csv\OtchToCSV;
use app\models\partner\stat\export\ExcerptToFile;
use app\models\partner\stat\export\ExportOtch;
use app\models\partner\stat\export\MfoMonthActs;
use app\models\partner\stat\export\OtchetPsXlsx;
use app\models\partner\stat\ExportExcel;
use app\models\partner\stat\PayShetStat;
use app\models\partner\stat\StatFilter;
use app\models\partner\stat\StatGraph;
use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\models\SendEmail;
use app\models\TU;
use app\modules\partner\models\DiffColumns;
use app\modules\partner\models\DiffData;
use app\modules\partner\models\DiffExport;
use app\modules\partner\models\forms\DiffColumnsForm;
use app\modules\partner\models\forms\DiffDataForm;
use app\modules\partner\models\forms\DiffExportForm;
use app\modules\partner\models\forms\ReverseOrderForm;
use app\modules\partner\models\PaySchetLogForm;
use app\services\ident\forms\IdentStatisticForm;
use app\services\ident\IdentService;
use app\services\partners\StatDiffSettingsService;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\jobs\RefundPayJob;
use app\services\payment\models\Bank;
use app\services\payment\models\PaySchet;
use app\services\payment\PaymentService;
use Exception;
use kartik\mpdf\Pdf;
use Throwable;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

use function serialize;

class StatController extends Controller
{
    public $enableCsrfValidation = false;

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
                        'allow' => false,
                        'roles' => ['@'],
                        'actions' => ['diff', 'diff-columns', 'diff-data', 'diff-export', 'recalc', 'recalcdata', 'recalc-save'],
                        'matchCallback' => function ($rule, $action) {
                            return !UserLk::IsAdmin(Yii::$app->user);
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'diffColumns' => ['POST'],
                    'diffData' => ['POST'],
                    'diffExport' => ['POST'],
                ],
            ],
        ];
    }

    public function actionDiff()
    {
        $banks = Bank::find()->all();

        return $this->render('diff', [
            'banks' => $banks,
        ]);
    }

    public function actionDiffColumns()
    {
        $form = new DiffColumnsForm();
        $form->load(Yii::$app->request->post(), '');

        if (!$form->validate()) {
            return $this->asJson([
                'status' => 0,
                'errors' => $form->getErrors(),
            ]);
        }

        $diffColumns = new DiffColumns();
        $dbColumns = $diffColumns->getDbColumns();

        $statDiffSettingsService = new StatDiffSettingsService();
        $settings = $statDiffSettingsService->getByBankId($form->bank);

        return $this->asJson([
            'status' => 1,
            'dbColumns' => $dbColumns,
            'settings' => $settings,
        ]);
    }

    public function actionDiffData()
    {
        $form = new DiffDataForm();
        $form->load(Yii::$app->request->post(), '');
        $form->registryFile = UploadedFile::getInstanceByName('registryFile');

        if (!$form->validate()) {
            return $this->asJson([
                'status' => 0,
                'errors' => $form->getErrors(),
            ]);
        }

        try {
            $diffData = new DiffData($form);
            [$badStatus, $notFound] = $diffData->execute();
        } catch (\Exception $e) {
            Yii::$app->errorHandler->logException($e);
            throw $e;
        }

        $statDiffSettingsService = new StatDiffSettingsService();
        $statDiffSettingsService->saveByForm($form);

        return $this->asJson([
            'status' => 1,
            'data' => $this->renderPartial('_diffdata', [
                'badStatus' => $badStatus,
                'notFound' => $notFound,
            ]),
        ]);
    }

    public function actionDiffExport()
    {
        $form = new DiffExportForm();
        $form->load(Yii::$app->request->post(), '');

        if (!$form->validate()) {
            return $this->asJson([
                'status' => 0,
                'errors' => $form->getErrors(),
            ]);
        }

        $diffExport = new DiffExport($form->getBadStatus(), $form->getNotFound());
        $diffExport->loadData();

        if ($form->format === 'csv') {
            $data = $diffExport->exportCsv();

            return Yii::$app->response->sendContentAsFile($data, 'export.csv', [
                'mimeType' => 'text/csv'
            ]);
        } else if ($form->format === 'xlsx') {
            $data = $diffExport->exportXlsx();

            return Yii::$app->response->sendContentAsFile($data, 'export.xlsx', [
                'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]);
        }

        throw new BadRequestHttpException();
    }

    /**
     * Продажи
     * @return string
     */
    public function actionList()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        return $this->render('list', [
            'IsAdmin' => $IsAdmin,
            'partnerlist' => $fltr->getPartnersList(),
            'uslugilist' => $fltr->getTypeUslugLiust()
        ]);
    }

    public function actionListdata()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $data = Yii::$app->request->post();
            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            $page = Yii::$app->request->get('page', 0);
            $payShetList = new PayShetStat();
            if ($payShetList->load($data, '') && $payShetList->validate()) {
                $list = $payShetList->getList2($IsAdmin, $page);
                return [
                    'status' => 1, 'data' => $this->renderPartial('_listdata', [
                        'reqdata' => $data,
                        'data' => $list['data'],
                        'cntpage' => $list['cntpage'],
                        'cnt' => $list['cnt'],
                        'pagination' => $list['pagination'],
                        'sumpay' => $list['sumpay'],
                        'sumcomis' => $list['sumcomis'],
                        'bankcomis' => $list['bankcomis'],
                        'voznagps' => $list['voznagps'],
                        'page' => $page,
                        'IsAdmin' => $IsAdmin
                    ])
                ];
            } else {
                return ['status' => 0, 'message' => $payShetList->GetError()];
            }
        } else {
            return $this->redirect('/partner');
        }
    }

    /**
     * Sends Excel file to client
     * @return void
     * @throws \app\models\partner\stat\exceptions\ExportExcelRawException
     */
    public function actionListexport(): void
    {
		ini_set('memory_limit', '8096M');
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        $payShetStat = new PayShetStat();
        try {
            if ($payShetStat->load(Yii::$app->request->get(), '') && $payShetStat->validate()) {
                $data = $payShetStat->getList2($IsAdmin, 0, 1);
                if (isset($data['data'])) {
                    $exportExcel = new ExportExcel();
                    $exportExcel->CreateXlsRaw(
                        "Экспорт",
                        $IsAdmin ? MfoStat::HEAD_ADMIN : MfoStat::HEAD_USER,
                        MfoStat::getDataGenerator($data['data'], $IsAdmin),
                        $IsAdmin ? MfoStat::ITOGS_ADMIN_EXCEL : MfoStat::ITOGS_USER_EXCEL
                    );
                }
            };
        } catch (Exception $e) {
            Yii::error(
                [
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTrace(),
                    $e->getPrevious(),
                    $payShetStat->getErrors(),
                    $payShetStat,
                    Yii::$app->request->get()
                ],
                __METHOD__
            );
            throw $e;
        }
    }

    /**
     * Responses Csv file to client
     * @return void|Response
     * @throws Exception
     */
    public function actionListExportCsv()
    {
		ini_set('memory_limit', '8096M');
        $isAdmin = UserLk::IsAdmin(Yii::$app->user);
        $payShetStat = new PayShetStat();
        try {
            if ($payShetStat->load(Yii::$app->request->get(), '') && $payShetStat->validate()) {
                $data = $payShetStat->getList2($isAdmin, 0, 1);
                if ($data) {
                    $exportCsv = new OtchToCSV($data);
                    $exportCsv->export();
                    return Yii::$app->response->sendFile($exportCsv->fullpath());
                }
            }
        } catch (Exception $e) {
            Yii::error(
                [
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTrace(),
                    $e->getPrevious(),
                    $payShetStat->getErrors(),
                    $payShetStat,
                    Yii::$app->request->get()
                ],
                __METHOD__
            );
            throw $e;
        }
    }

    public function actionRecalc()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        return $this->render('recalc', [
            'IsAdmin' => $IsAdmin,
            'partnerlist' => $fltr->getPartnersList(),
            'uslugilist' => $fltr->getTypeUslugLiust(),
            'bankList' => $fltr->getBankList(),
        ]);
    }

    public function actionRecalcdata()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $data = Yii::$app->request->post();
            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            $page = Yii::$app->request->get('page', 0);
            $payShetList = new PayShetStat();
            if ($payShetList->load($data, '') && $payShetList->validate()) {
                $list = $payShetList->getList2($IsAdmin, $page);
                return [
                    'status' => 1, 'data' => $this->renderPartial('_recalcdata', [
                        'reqdata' => $data,
                        'data' => $list['data'],
                        'cntpage' => $list['cntpage'],
                        'cnt' => $list['cnt'],
                        'pagination' => $list['pagination'],
                        'sumpay' => $list['sumpay'],
                        'sumcomis' => $list['sumcomis'],
                        'bankcomis' => $list['bankcomis'],
                        'voznagps' => $list['voznagps'],
                        'page' => $page,
                        'IsAdmin' => $IsAdmin
                    ])
                ];
            } else {
                return ['status' => 0, 'message' => $payShetList->GetError()];
            }
        } else {
            return $this->redirect('/partner');
        }
    }

    public function actionRecalcSave()
    {
        if (Yii::$app->request->isAjax) {
            $payShetList = new PayShetStat();
            if ($payShetList->load(Yii::$app->request->get(), '')) {
                $data = $payShetList->getList2(UserLk::IsAdmin(Yii::$app->user), 0, 1);
                $paySchets = PaySchet::findAll(ArrayHelper::getColumn($data['data'], 'ID'));
                foreach ($paySchets as $paySchet) {
                    $paySchet->recalcComiss(array_map('floatval', Yii::$app->request->post()));
                    $paySchet->save(false);
                }
                return $this->asJson([
                    'status' => 1,
                    'count' => count($paySchets),
                ]);
            }
            return $this->asJson([
                'status' => 0,
                'count' => 0,
            ]);

        }
        return $this->redirect('/partner');
    }

    /**
     * Отменить платеж
     * @return array|Response
     */
    public function actionReversorder()
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect('/partner');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new ReverseOrderForm();
        $form->load(Yii::$app->request->bodyParams, '');
        $form->idOrg = UserLk::IsAdmin(Yii::$app->user) ? 0 : UserLk::getPartnerId(Yii::$app->user);
        if (!$form->validate()) {
            return $this->asJson([
                'status' => 0,
                'errors' => $form->getErrors(),
            ]);
        }

        Yii::$app->queue->push(new RefundPayJob([
            'paySchetId' => $form->id,
            'refundSum' => $form->refundSum,
            'initiator' => Yii::$app->user->getId() ?? 'actionReversOrder',
        ]));

        return ['status' => 1, 'message' => 'Ожидается отменена'];
    }

    /**
     * Сбросить статус платежа
     * @return array
     */
    public function actionUpdateStatusPay()
    {
        if (!Yii::$app->request->isAjax || !UserLk::IsAdmin(Yii::$app->user)) {
            return ['status' => 2, 'message' => 'Ошибка запроса'];
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $paySchet = PaySchet::findOne([
            'ID' => (int)Yii::$app->request->post('id', 0),
        ]);

        if(!$paySchet) {
            return ['status' => 2, 'message' => 'Ошибка запроса'];
        }

        $paySchet->Status = 0;
        $paySchet->ErrorInfo = 'Запрашивается статус';
        $paySchet->sms_accept = 1;

        if($paySchet->save(false)) {
            return ['status' => 1, 'message' => 'Статус сброшен, ожидается обновление'];
        } else {
            return ['status' => 2, 'message' => 'Ошибка обновления'];
        }
    }

    public function actionOtch()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        return $this->render('otch', [
            'IsAdmin' => $IsAdmin,
            'partnerlist' => $fltr->getPartnersList(),
            'magazlist' => $IsAdmin ? $fltr->getMagazList(-1) : $fltr->getMagazList(UserLk::getPartnerId(Yii::$app->user)),
            'uslugilist' => $fltr->getTypeUslugLiust(),
            'bankList' => $fltr->getBankList(),
        ]);
    }

    public function actionOtchdata()
    {
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            $payShetList = new PayShetStat();
            Yii::warning('partner/stat/otchdata POST: ' . serialize($data), 'partner');
            try {
                if ($payShetList->load($data, '') && $payShetList->validate()) {
                    $data = $payShetList->getOtch($IsAdmin);
                    return $this->renderPartial('_otchdata', [
                        'IsAdmin' => $IsAdmin,
                        'data' => $data,
                        'requestToExport' => $payShetList->getAttributes()
                    ]);
                }
            } catch (Throwable $e) {
                Yii::warning('partner/stat/otchdata POST: ' . serialize($data) . '; Exception: ' . $e->getMessage() . '; trace: ' . $e->getTraceAsString(), 'partner');
                return '';
            }
        }
        return $this->redirect('/partner');
    }

    public function actionExportOtch()
    {
        $export = new ExportOtch(
            new ExportExcel(),
            new PayShetStat()
        );
        if ($export->successful()) {
            Yii::$app->response->format = Response::FORMAT_RAW;
            Yii::$app->response->setDownloadHeaders(
                "export.xlsx",
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            );
            return $export->content();
        }
        return $this->redirect('/partner');
    }

    public function actionExcerpt()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $excerpt = Excerpt::buildAjax('id');
            if ($excerpt->data()) {
                return [
                    'status' => 1,
                    'data' => $this->renderPartial("excerpt/_excerpt", ['data' => $excerpt->data()]),
                    'message' => $excerpt->excerptName()// можно получить из ->data().
                ];
            }
            return ['status' => 0, 'message' => 'Не удалось получить выписку, возможно у вас нет доступа к этой информации.'];
        }
        return [''];
    }

    public function actionLog()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $paySchetLogForm = new PaySchetLogForm();
            $paySchetLogForm->load(Yii::$app->request->post(), '');

            if(!$paySchetLogForm->validate()) {
                return ['status' => 0, 'message' => $paySchetLogForm->GetError()];
            }

            /** @var PaymentService $paymentService */
            $paymentService = Yii::$container->get('PaymentService');
            $paySchetLogData = $paymentService->geyPaySchetLog($paySchetLogForm);

            return [
                'status' => 1,
                'data' => $this->renderPartial("_log", ['data' => $paySchetLogData]),
                'message' => '',
            ];

        }
        return [''];

    }

    /**
     * Экспорт выписки по операции.
     * @param $id
     * @return mixed
     */
    public function actionExportExcerpt($id)
    {
        $export = ExcerptToFile::buildSimpleOrderId((int)$id);
        return $export->content();
    }

    public function actionSendExcerpt()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new DynamicModel(['id', 'email']);
        $model->addRule(['email'], 'email')
            ->addRule(['id'], 'integer');
        $model->load(Yii::$app->request->post(), '');
        if ($model->validate()) {
            $content = "Выписка представлена в виде прикрепленного файла pdf.";
            $excerpt = ExcerptToFile::buildSimpleOrderId($model->id);
            $mail = new SendEmail();
            $mail->sendReestr(
                $model->email,
                "Выписка по операции " . $model->id,
                $content,
                [[
                    'data' => $excerpt->content(),
                    'name' => "order_" . $model->id . '.pdf'
                ]]
            );
            Yii::$app->response->format = Response::FORMAT_JSON; //конвертер зачем то меняет формат ответа
            return ['status' => 1, 'message' => 'Сообщение успешно отправлено.'];
        }
        return ['status' => 0, 'message' => 'Не верно введены данные.', 'data' => $model->errors];

    }

    public function actionSale()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        return $this->render('sale', [
            'uslugilist' => $fltr->getTypeUslugLiust(TU::InAll())
        ]);
    }

    /**
     * @return array|Response
     * @throws \Throwable
     */
    public function actionSaledata()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $StatGraph = new StatGraph();
            $StatGraph->load(Yii::$app->request->post(), '');
            if ($StatGraph->validate()) {
                return $StatGraph->GetSale();
            }
            return ['status' => 0, 'message' => $StatGraph->GetError()];
        } else {
            return $this->redirect('/partner');
        }
    }

    public function actionSaledraft()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        return $this->render('saledraft', [
            'uslugilist' => $fltr->getTypeUslugLiust(TU::InAll())
        ]);
    }

    /**
     * @return array|Response
     * @throws \Throwable
     */
    public function actionSaledraftdata()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $StatGraph = new StatGraph();
            $StatGraph->load(Yii::$app->request->post(), '');
            if ($StatGraph->validate()) {
                return $StatGraph->GetSaleDraft();
            }
            return ['status' => 0, 'message' => $StatGraph->GetError()];
        } else {
            return $this->redirect('/partner');
        }
    }

    public function actionSalekonvers()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        return $this->render('salekonvers', [
            'uslugilist' => $fltr->getTypeUslugLiust(TU::InAll())
        ]);
    }

    /**
     * @return array|Response
     * @throws \Throwable
     */
    public function actionSalekonversdata()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $StatGraph = new StatGraph();
            $StatGraph->load(Yii::$app->request->post(), '');
            if ($StatGraph->validate()) {
                return $StatGraph->GetSaleKonvers();
            }
            return ['status' => 0, 'message' => $StatGraph->GetError()];
        } else {
            return $this->redirect('/partner');
        }
    }

    public function actionPlatelshik()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        return $this->render('platelshik', [
            'uslugilist' => $fltr->getTypeUslugLiust(TU::InAll())
        ]);
    }

    /**
     * @return array|Response
     * @throws \Throwable
     */
    public function actionPlatelshikdata()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $StatGraph = new StatGraph();
            $StatGraph->load(Yii::$app->request->post(), '');
            if ($StatGraph->validate()) {
                return $StatGraph->GetPlatelshikData();
            }
            return ['status' => 0, 'message' => $StatGraph->GetError()];
        } else {
            return $this->redirect('/partner');
        }
    }

    /**
     * @return string
     */
    public function actionRecurrentcard()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        $uslugilist = $IsAdmin ? $fltr->getUslugList(-1, TU::AutoPay()) : $fltr->getUslugList(UserLk::getPartnerId(Yii::$app->user), TU::AutoPay());
        return $this->render('recurrentcard', [
            'name' => 'Регулярные платежи',
            'IsAdmin' => $IsAdmin,
            'partnerlist' => $fltr->getPartnersList(),
            'uslugilist' => $uslugilist,
        ]);

    }

    /**
     * @return array|Response
     */
    public function actionRecurrentcarddata()
    {
        if (Yii::$app->request->isAjax) {
            $AutopayStat = new AutopayStat();
            if ($AutopayStat->loadAndValidate(Yii::$app->request->post(), '')) {
                return $this->asJson([
                    'status' => 1,
                    'data' => $this->renderPartial('_recurrentcarddata', ['data' => $AutopayStat->getData()])
                ]);
            }
            return $this->asJson(['status' => 0, 'message' => $AutopayStat->GetError()]);
        }
        return $this->redirect('/partner');
    }

    /**
     * Акты
     * @return string|Response
     */
    public function actionActs()
    {
        $fltr = new StatFilter();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        return $this->render('acts/index', [
            'IsAdmin' => $IsAdmin,
            'partnerlist' => $fltr->getPartnersList(false, true)
        ]);
    }

    /**
     * Содержимое акта
     * @return array|Response
     */
    public function actionActList()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            $model = new MfoMonthActs();
            $model->load(Yii::$app->request->post(), '');
            if (!$model->validate()) {
                return ['status' => 0, 'message' => $model->GetError()];
            }

            return [
                'status' => 1,
                'data' => $this->renderPartial('acts/_list', [
                    'IsAdmin' => $IsAdmin,
                    'acts' => $model->GetList($IsAdmin)
                ])
            ];
        }
        return $this->redirect('/partner');
    }

    /**
     * Содержимое акта
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionActEdit($id)
    {
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        if ($IsAdmin) {

            $act = ActMfo::findOne(['ID' => (int)$id]);
            if (!$act) {
                throw new NotFoundHttpException();
            }

            return $this->render('acts/edit', [
                'act' => $act
            ]);
        }
        return $this->redirect('/partner');
    }

    /**
     * Сформировать акт
     * @return array|Response
     * @throws \yii\db\Exception
     */
    public function actionActCreate()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model = new MfoMonthActs();
            $model->load(Yii::$app->request->post(), '');
            if (!$model->validate()) {
                return ['status' => 0, 'message' => $model->GetError()];
            }

            if ($model->CreateActs()) {
                return [
                    'status' => 1,
                    'message' => 'Акты сформированы'
                ];
            }
            return ['status' => 0, 'message' => 'Ошибка формирования актов'];

        }
        return $this->redirect('/partner');
    }

    public function actionActPub()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model = new MfoMonthActs();
            $model->load(Yii::$app->request->post(), '');
            if (!$model->validate()) {
                return ['status' => 0, 'message' => $model->GetError()];
            }

            if ($model->PubActs()) {
                return [
                    'status' => 1,
                    'message' => 'Акты сформированы'
                ];
            }
            return ['status' => 0, 'message' => 'Ошибка публикования актов'];

        }
        return $this->redirect('/partner');
    }

    public function actionActSave()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $model = ActMfo::findOne(['ID' => Yii::$app->request->post('ID')]);
            if (!$model) {
                return ['status' => 0, 'message' => 'Ошибка запроса'];
            }

            $model->load(Yii::$app->request->post(), 'ActMfo');
            $model->SumEditToKop();
            if (!$model->validate()) {
                return ['status' => 0, 'message' => $model->GetError()];
            }
            if ($model->save(false)) {
                $MfoMonthActs = new MfoMonthActs();
                $MfoMonthActs->SaveXlsDocument($model);
                return [
                    'status' => 1,
                    'message' => 'Акт сохранен'
                ];
            }
            return ['status' => 0, 'message' => 'Ошибка сохранения акта'];

        }
        return $this->redirect('/partner');
    }

    /**
     * Акт МФО XLS
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function actionActsXls($id)
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $act = ActMfo::findOne(['ID' => (int)$id]);
        } else {
            $act = ActMfo::findOne(['ID' => (int)$id, 'IdPartner' => UserLk::getPartnerId(Yii::$app->user), 'IsPublic' => 1]);
        }
        if ($act) {
            $model = new MfoMonthActs();
            $content = $model->GetXlsDocument($act);
            if ($content) {
                Yii::$app->response->format = Response::FORMAT_RAW;
                Yii::$app->response->setDownloadHeaders(
                    $content['name'],
                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                );
                return $content['data'];
            }
        }
        throw new NotFoundHttpException();
    }

    public function actionActsPp()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $act = ActMfo::findOne(['ID' => (int)Yii::$app->request->get('ID')]);
            if ($act) {
                $model = new MfoMonthActs();
                $content = $model->GetPPDocument($act);
                if ($content) {
                    Yii::$app->response->format = Response::FORMAT_RAW;
                    Yii::$app->response->setDownloadHeaders(
                        $content['name'],
                        "text/plain"
                    );
                    return $content['data'];
                }
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * Выставить счет
     * @return array|Response
     * @throws \yii\db\Exception
     */
    public function actionActCreateschet()
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {

            Yii::$app->response->format = Response::FORMAT_JSON;

            $act = ActMfo::findOne(['ID' => (int)Yii::$app->request->post('ActId')]);
            if (!$act) {
                return ['status' => 0, 'message' => 'Акт не найден'];
            }

            $schet = new ActSchet();
            $schet->IdPartner = $act->IdPartner;
            $schet->IdAct = $act->ID;
            $schet->NumSchet = $schet->GetNextNum();
            $schet->SumSchet = $act->SumSchetComisVyplata;
            $schet->DateSchet = time();
            $schet->Komment = 'Вознаграждение за период, '.date('m.Y', $act->ActPeriod);
            $schet->IsDeleted = 0;
            if ($schet->save(false)) {

                $vv = new VyvodVoznag();
                $vv->setAttributes([
                    'partner' => $act->IdPartner,
                    'summ' => $act->ComisVyplata,
                    'datefrom' => date("01.m.Y H:i", $act->ActPeriod),
                    'dateto' => date("t.m.Y 23:59", $act->ActPeriod),
                    'isCron' => true,
                    'type' => 1,
                    'balance' => 0
                ]);
                $vv->CreatePayVyvod();

                return [
                    'status' => 1,
                    'message' => 'Счет создан'
                ];
            }
            return ['status' => 0, 'message' => 'Ошибка сохранения счета'];

        }
        return $this->redirect('/partner');
    }

    public function actionActsSchetget($id)
    {
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $schet = ActSchet::findOne(['ID' => $id]);

            if ($schet) {
                $Partner = Partner::findOne(['ID' => $schet->IdPartner]);
                $recviz = VyvodVoznag::GetRecviz();

                $pdf = new Pdf([
                    'mode'=> Pdf::MODE_UTF8,
                    'format' => Pdf::FORMAT_A4,
                    'orientation' => Pdf::ORIENT_PORTRAIT,
                    'destination' => Pdf::DEST_DOWNLOAD,
                    'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
                    'cssInline' => '.table-acc{
                        border-collapse: collapse;
                    }
                    .table-acc tr td{
                        border: 1px solid #000000;
                        padding: 3px;
                        font-size:14px;
                    }'
                ]);
                $pdf->content = $this->renderPartial('@app/modules/partner/views/stat/acts/_fileschet.php', [
                    'Partner' => $Partner,
                    'recviz' => $recviz,
                    'Schet' => $schet
                ]);
                $pdf->filename = 'schet_'.$schet->ID.'.pdf';

                Yii::$app->response->format = Response::FORMAT_RAW;
                return $pdf->render();
            }

        }
        throw new NotFoundHttpException();
    }

    public function actionDraft($id)
    {
        $kkt = new OnlineKassa();
        $ret = $kkt->printFromDB($id);
        if (!empty($ret)) {
            return $ret;
        }
        throw new NotFoundHttpException();
    }

    public function actionOtchetps()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        $datefrom = Yii::$app->request->get('datefrom');
        $dateto = Yii::$app->request->get('dateto');
        if (UserLk::IsAdmin(Yii::$app->user)) {
            $partner = Yii::$app->request->get('IdPart', 0);
        } else {
            $partner = UserLk::getPartnerId(Yii::$app->user);
        }
        $OtchetPs = new OtchetPsXlsx($datefrom, $dateto, $partner);
        $content = $OtchetPs->RenderContent();
        Yii::$app->response->setDownloadHeaders(
            $content['name'],
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        );
        return $content['data'];
    }

    public function actionIdent()
    {
        $partners = null;
        $data = null;

        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        if ($IsAdmin) {
            $partners = ArrayHelper::index(
                Partner::find()->select(['ID', 'Name'])->where(['IsBlocked' => 0, 'IsDeleted' => 0])->all(), 'ID'
            );
        } else {
            $partners = [];
        }

        return $this->render('ident', compact('partners'));

    }

    public function actionIdentProcessing()
    {
        $this->enableCsrfValidation = false;
        $post = Yii::$app->request->post();

        $identStatisticForm = new IdentStatisticForm();
        $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
        if (!$IsAdmin) {
            $post['filters']['partnerId'] = UserLk::getPartnerId(Yii::$app->user);
        }

        if(!$identStatisticForm->load($post, '') || !$identStatisticForm->validate()) {
            $a = 0;
        }
        return $this->asJson($this->getIdentService()->getIdentStatistic($identStatisticForm));
    }

    /**
     * @return IdentService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    private function getIdentService()
    {
        return Yii::$container->get('IdentService');
    }
}
