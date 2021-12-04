<?php

namespace app\services\reportService\dataObjects;

use app\services\ReportService;
use Carbon\Carbon;

/**
 * Data transfer object for report creation.
 */
interface CreateReportData
{
    /**
     * @return Carbon Date of transactions which should be in the report.
     */
    public function getDate(): Carbon;

    /**
     * @return int|null Status of transactions which should be in the report.
     * @see ReportService::TRANSACTION_STATUSES
     */
    public function getTransactionStatus(): ?int;

    /**
     * @return int[]|null Array of service type (uslugatovar type) IDs.
     */
    public function getServiceTypeIds(): ?array;
}