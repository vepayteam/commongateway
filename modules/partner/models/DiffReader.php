<?php

namespace app\modules\partner\models;

use app\modules\partner\models\readers\DiffReadColumnsFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class DiffReader
{
    private const CSV_READER_TYPE = 'Csv';
    private const CSV_DELIMITER = ';';

    private $dbColumns = [
        'ID', 'RRN', 'ExtBillNumber'
    ];

    /**
     * @var string
     */
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Возвращает первую строку фала с названиями колонок
     *
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getRegistryColumns(): array
    {
        $rows = $this->readActiveSheet(new DiffReadColumnsFilter());
        [$columns] = $rows;

        return range(1, count($columns));
    }

    public function getDbColumns(): array
    {
        return $this->dbColumns;
    }

    /**
     * @param IReadFilter|null $readFilter
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function readActiveSheet(IReadFilter $readFilter = null): array
    {
        $fileFormat = IOFactory::identify($this->filename);
        $reader = IOFactory::createReader($fileFormat);
        $reader->setReadDataOnly(true);

        if ($readFilter !== null) {
            $reader->setReadFilter($readFilter);
        }

        if ($fileFormat === self::CSV_READER_TYPE) {
            $reader->setDelimiter(self::CSV_DELIMITER);
        }

        $spreadsheet = $reader->load($this->filename);
        return $spreadsheet->getActiveSheet()->toArray();
    }
}
