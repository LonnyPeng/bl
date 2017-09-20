<?php

namespace App\Utils;

interface PaginatorInterface
{
    /**
     * Set list items count
     *
     * @param int $count
     */
    public function setTotalItemCount($count);

    /**
     * Set pagesize number
     *
     * @param int $count
     */
    public function setItemCountPerPage($count);

    /**
     * Set current page number
     *
     * @param int $number
     */
    public function setCurrentPageNumber($number);

    /**
     * Get items count
     *
     * @return int
     */
    public function getTotalItemCount();

    /**
     * Get pagesize number
     *
     * @return int
     */
    public function getItemCountPerPage();

    /**
     * Get page count
     *
     * @return int
     */
    public function getPageCount();

    /**
     * get current page number
     *
     * @return int
     */
    public function getCurrentPageNumber();

    /**
     * get Previous page number
     *
     * @return int|bool
     */
    public function getPreviousPageNumber();

    /**
     * get next page number
     *
     * @return int|bool
     */
    public function getNextPageNumber();

    /**
     * Get page range lists
     *
     * @param int $display how many page should display
     * @return array
     */
    public function getPageRange($display = 10);

    /**
     * Get first item from current page for SQL
     *
     * @return int
     */
    public function getLimitStart();
}