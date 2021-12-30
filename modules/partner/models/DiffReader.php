<?php

namespace app\modules\partner\models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class DiffReader
{
    private const CSV_READER_TYPE = 'Csv';
    private const CSV_DELIMITER = ';';

    /**
     * @var string
     */
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
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
