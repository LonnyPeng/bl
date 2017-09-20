<?php

namespace App\Csv;

interface CsvExportInterface
{
    /**
     * set the header of csv.
     *
     * @param array $header
     * @return CsvExportInterface
     */
    public function setHeader(array $header);

    /**
     * append data to the csv file
     *
     * @param array $rows
     * @param boolean $autoHeader when  autoHeader is true , we dont't nedd the method setHeader
     * @return CsvExportInterface
     */
    public function append(array $rows, $autoHeader = false);

    /**
     * download the csv file
     *
     * @param string|null $file
     * @param string $delimiter
     * @param string $enclosure
     * @return boolean
     */
    public function export($file = null, $delimiter = '\t', $enclosure = '"');
}