<?php

namespace app\modules\mfo\controllers;

use app\models\mfo\MfoReq;
use app\models\Report;
use app\modules\mfo\components\BaseApiController;
use app\modules\mfo\jobs\FillReportJob;
use app\modules\mfo\models\CreateReportForm;
use app\services\payment\models\UslugatovarType;
use app\services\ReportService;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\queue\redis\Queue;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Transaction report.
 */
class ReportController extends BaseApiController
{
    private const CREATION_TIMEOUT = 60 * 5; // 5 minutes

    /**
     * @var ReportService
     */
    private $reportService;
    /**
     * @var Queue
     */
    private $reportQueue;

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->reportService = \Yii::$app->get(ReportService::class);
        $this->reportQueue = \Yii::$app->get('reportQueue');
    }

    /**
     * Creates a transaction report.
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionCreate(): array
    {
        $mfo = new MfoReq();
        $mfo->LoadData(\Yii::$app->request->getRawBody());
        $partner = $mfo->getPartner();

        $form = new CreateReportForm($partner);
        $form->load($mfo->Req(), '');
        if (!$form->validate()) {
            return [
                'status' => 0,
                'message' => 'Ошибка валидации: ' . $this->getError($form),
            ];
        }

        if (!$this->reportService->checkCreateTimeout($partner, static::CREATION_TIMEOUT)) {
            return [
                'status' => 0,
                'message' => 'Минимальный интервал для запроса - ' . ceil(static::CREATION_TIMEOUT / 60) . ' минут.',
            ];
        }

        $report = $this->reportService->create($form, $partner);

        $this->reportQueue->push(new FillReportJob(['reportId' => $report->Id]));

        return [
            'status' => 1,
            'message' => 'Запрос на формирование отчёта создан.',
            'id' => $report->Id,
        ];
    }

    /**
     * Report status.
     *
     * @throws \yii\db\Exception
     * @throws UnauthorizedHttpException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionState(): array
    {
        $mfo = new MfoReq();
        $mfo->LoadData(\Yii::$app->request->getRawBody());

        $reportId = $mfo->GetReq('id');
        $partnerId = $mfo->getPartner()->ID;

        $report = Report::findOne([
            'Id' => $reportId,
            'PartnerId' => $partnerId,
        ]);
        if ($report === null) {
            \Yii::warning("Report not found (report ID: {$reportId}, partner ID: {$partnerId}, route: {$this->route}).");
            throw new NotFoundHttpException('Отчёт не найден.');
        }

        return [
            'status' => $report->Status,
            'message' => [
                Report::STATUS_IN_PROCESS => 'Отчёт в процессе формирования.',
                Report::STATUS_COMPLETED => 'Отчёт сформирован.',
                Report::STATUS_ERROR => 'Ошибка.',
            ][$report->Status],
        ];
    }

    /**
     * Returns a list of service type IDs which allowed for the specified merchant.
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\db\Exception
     */
    public function actionGetServiceTypes(): array
    {
        $mfo = new MfoReq();
        $mfo->LoadData(\Yii::$app->request->getRawBody());

        return [
            'status' => 0,
            'service_types' => $this->reportService->getAllowedServiceTypes($mfo->getPartner()),
        ];
    }

    /**
     * Paginated list of transactions in the specified report.
     *
     * @return ActiveDataProvider
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\db\Exception
     */
    public function actionGetTransactionList(): ActiveDataProvider
    {
        $mfo = new MfoReq();
        $mfo->LoadData(\Yii::$app->request->getRawBody());

        $reportId = $mfo->GetReq('id');
        $partnerId = $mfo->getPartner()->ID;

        $report = Report::findOne([
            'Id' => $mfo->GetReq('id'),
            'PartnerId' => $mfo->getPartner()->ID,
        ]);
        if ($report === null) {
            \Yii::warning("Report not found (report ID: {$reportId}, partner ID: {$partnerId}, route: {$this->route}).");
            throw new NotFoundHttpException('Отчёт не найден.');
        }

        $page = $mfo->GetReq('page');
        if (!is_numeric($page) || $page < 0) {
            $page = 0;
        }

        return $this->reportService->getTransactionList($report, (int)$page);
    }

    /**
     * Returns the first validation error.
     *
     * @param Model $model
     * @return string|null
     */
    private function getError(Model $model): ?string
    {
        $errors = $model->getFirstErrors();
        return array_shift($errors);
    }
}