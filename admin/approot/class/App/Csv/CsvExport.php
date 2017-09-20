<?php

namespace App\Csv;

/**
 * @example
 *   $export = new CsvExport();
 */
class CsvExport implements CsvExportInterface
{
    /**
     * the header of csv files
     *
     *  @var array
     */
    private $header = array();

    /**
     * the rows of csv files
     *
     * @var array
     */
    private $rows = array();

    /**
     * Set the header of Csv
     *
     * @param array $header
     * @return CsvExport
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * Append data to csv file
     *
     * @param array $rows
     * @param boolean $autoHeader  auto get the header of Csv
     * @return CsvExport
     */
    public function append(array $rows, $autoHeader = false)
    {
        foreach ($rows as $row) {
            $this->rows[] = array_map('setText', $row);
        }

        if (true === $autoHeader && !empty($rows[0])) {

            $this->header = array_keys($rows[0]);
        }
        return $this;
    }

    /**
     * Export the csv file
     *
     * @param string|null $file
     * @param string $delimiter
     * @param string $enclosure
     * @return boolean
     */
    public function export($file = null, $delimiter = "\t", $enclosure = '"')
    {
        if (null === $file) {
            $file = time() . '.csv';
        }

        $handle = fopen('php://output', 'w');
        array_unshift($this->rows, $this->header);

        header("Content-type:text/csv");
        header('Content-Disposition: attachment; filename=' . basename($file));
        echo chr(239) . chr(187) . chr(191);
        foreach ($this->rows as $row) {
            if (empty($row)) {
                continue;
            }
            fputcsv($handle, $row);
        }
        fclose($handle);
        return true;
    }
}