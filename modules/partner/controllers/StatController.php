<?php

namespace app\modules\partner\controllers;

use app\models\bank\BankMerchant;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
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
use app\models\Payschets;
use app\models\queue\JobPriorityInterface;
use app\models\queue\SendMailJob;
use app\models\SendEmail;
use app\models\TU;
use app\modules\partner\models\DiffData;
use app\modules\partner\models\DiffExport;
use app\modules\partner\models\PaySchetLogForm;
use app\services\ident\forms\IdentStatisticForm;
use app\services\ident\IdentService;
use app\services\payment\jobs\RefundPayJob;
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
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;
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
                    'diffdata' => ['POST'],
                ],
            ],
        ];
    }

    public function actionDiff()
    {
        return $this->render('diff');
    }

    public function actionDiffdata()
    {
        $registryFile = UploadedFile::getInstanceByName('registryFile');

        try {
            $diffData = new DiffData();
            $diffData->read($registryFile->tempName);

            [$badStatus, $notFound] = $diffData->execute();
        } catch (Exception $e) {
            Yii::warning('Stat diffData exception '
                . $registryFile->tempName
                . ': ' . $e->getMessage()
            );
            throw new BadRequestHttpException();
        }

        return $this->asJson([
            'status' => 1,
            'data' => $this->renderPartial('_diffdata', [
                'badStatus' => $badStatus,
                'notFound' => $notFound,
            ]),
        ]);
    }

    public function actionDiffexport()
    {
        $badStatus = json_decode(Yii::$app->request->post('badStatus'), true);
        $notFound = json_decode(Yii::$app->request->post('notFound'), true);
        $format = Yii::$app->request->post('format');

        $diffExport = new DiffExport($badStatus, $notFound);
        $diffExport->prepareData();

        if ($format === 'csv') {
            $data = $diffExport->exportCsv();

            return Yii::$app->response->sendContentAsFile($data, 'export.csv', [
                'mimeType' => 'text/csv'
            ]);
        } else if ($format === 'xlsx') {
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

    public function actionListexport()
    {
		ini_set('memory_limit', '1024M');
        $MfoStat = new MfoStat();
        $MfoStat->ExportOpListRaw(Yii::$app->request->get());
    }

    public function actionListExportCsv()
    {
		ini_set('memory_limit', '1024M');
        $isAdmin = UserLk::IsAdmin(Yii::$app->user);
        $payschet = new PayShetStat(); //загрузить
        if ($payschet->load(Yii::$app->request->get(), '') && $payschet->validate()){
            $data = $payschet->getList2($isAdmin,0,1);
            if ($data){
                $file = new OtchToCSV($data);
                $file->export();
                return Yii::$app->response->sendFile($file->fullpath());
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * Отменить платеж
     * @return array|Response
     * @throws \yii\db\Exception
     */
    public function actionReversorder()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $org = UserLk::IsAdmin(Yii::$app->user) ? 0 : UserLk::getPartnerId(Yii::$app->user);

            $where = [
                'ID' => Yii::$app->request->post('id', 0),
            ];

            if($org) {
                $where['IdOrg'] = $org;
            }
            if(PaySchet::find()->where($where)->exists()) {
                Yii::$app->queue->push(new RefundPayJob([
                    'paySchetId' => Yii::$app->request->post('id', 0),
                ]));
            }

            return ['status' => 1, 'message' => 'Ожидается отменена'];
        } else {
            return $this->redirect('/partner');
        }
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
            'uslugilist' => $fltr->getTypeUslugLiust()
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
            'name' => 'Автоплатежи',
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
            $IsAdmin = UserLk::IsAdmin(Yii::$app->user);
            Yii::$app->response->format = Response::FORMAT_JSON;
            $AutopayStat = new AutopayStat();
            $AutopayStat->load(Yii::$app->request->post(), '');
            if ($AutopayStat->validate()) {
                return [
                    'status' => 1,
                    'data' => $this->renderPartial('_recurrentcarddata', [
                        'data' => $AutopayStat->GetData($IsAdmin)
                    ])
                ];
            }
            return ['status' => 0, 'message' => $AutopayStat->GetError()];
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
