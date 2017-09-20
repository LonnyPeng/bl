<?php

namespace App\Csv;

/**
 * @example
 *  $import = new CsvImport();
 *  $import->setFile('read.csv',1000,',');
 *  print_r($import->getRows());
 *  print_r($import->getHeader());
 */
class CsvImport implements CsvImportInterface
{
    /**
     * the header of csv files
     *
     * @var array
     */
    private $header = array();

    /**
     * the rows of csv files
     *
     * @var array
     */
    private $rows = array();

    /**
     * the row's line of csv file
     *
     * @var numeric
     */
    private $line;

    /**
     * whether it has got header of csv file
     *
     * @var boolean
     */
    private $init;

    /**
     * the field delimiter (one character only).
     *
     * @var string
     */
    private $delimiter;

    /**
     * the field enclosure character (one character only)
     *
     * @var string
     */
    private $enclosure;

    /**
     *  a file pointer resource
     *
     * @var resource
     */
    private $handle;

    /**
     * set the infomation of csv file
     *
     * @param string $file
     * @param int $length
     * @param string $delimiter
     * @param string $enclosure
     * @return CsvImport
     */
    public function setFile($file, $length = 1000, $delimiter = "\t", $enclosure = '"')
    {
        if (!file_exists($file)) {
            return false;
        }

        $this->handle = fopen($file, 'r+');
        $this->length = $length;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->line = 0;
        return $this;
    }

    /**
     * get the header of csv file
     *
     * @return array
     */
    public function getHeader()
    {
        $this->init();
        return $this->header;
    }

    /**
     * get the rows of csv file
     *
     * @return array  array(array('key'=>'value'));
     */
    public function getRows()
    {
        $data = array();
        while ($row = $this->getRow()) {
            $data[] = $row;
        }
        return $data;
    }

    private function getRow()
    {
        $this->init();
        if (($row = fgetcsv($this->handle, $this->length, $this->delimiter, $this->enclosure)) !== false) {
            $row = array_map('setImport', $row);
            $this->line++;
            return $this->header ? array_combine($this->header, $row) : $row;
        } else {
            return false;
        }
    }

    // the purpose : get header
    private function init()
    {
        if (true === $this->init) {
            return;
        }
        $this->init = true;
        $this->header = $this->getRow();
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}
