<?php

namespace app\models\partner\stat\export\phpExportData;

/**
 * ExportDataCSV - Exports to CSV (comma separated value) format.
 */
class ExportDataCsv extends ExportData
{
    public function sendHttpHeaders(): void
    {
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=" . basename($this->filename));
    }

    protected function generateHeader(): string
    {
        return ''; // TODO: Implement generateHeader() method.
    }

    protected function generateFooter(): string
    {
        return ''; // TODO: Implement generateFooter() method.
    }

    protected function generateRow(array $row): string
    {
        foreach ($row as $key => $value) {
            // Escape inner quotes and wrap all contents in new quotes.
            // Note that we are using \" to escape double quote not ""
            $row[$key] = '"' . str_replace('"', '\"', $value) . '"';
        }

        return implode(",", $row) . "\n";
    }
}

