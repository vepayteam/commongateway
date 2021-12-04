<?php

namespace app\modules\mfo\jobs;

use app\models\Report;
use app\services\ReportService;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Fills the report with transaction items.
 */
class FillReportJob extends BaseObject implements JobInterface
{
    /**
     * @var int
     */
    public $reportId;

    /**
     * {@inheritDoc}
     * @throws \Throwable
     */
    public function execute($queue)
    {
        /** @var ReportService $reportService */
        $reportService = \Yii::$app->get(ReportService::class);

        $report = Report::findOne($this->reportId);
        $reportService->fillReport($report);
    }
}