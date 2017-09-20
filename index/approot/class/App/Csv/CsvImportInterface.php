<?php

namespace App\Csv;

interface CsvImportInterface
{
    /**
     * set the infomation of csv file
     *
     * @param string $file
     * @param int $length
     * @param string $delimiter
     * @param string $enclosure
     * @return CsvImportInterface
     */
    public function setFile($file, $length = 1000, $delimiter = '\t', $enclosure = '"');

    /**
     * get the header of csv file
     *
     * @return array
     */
    public function getHeader();

    /**
     *  get the rows of csv file
     *
     * @return array
     */
    public function getRows();
}