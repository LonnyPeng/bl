<?php

namespace App\Utils;

class Paginator implements PaginatorInterface
{

    protected $itemCountTotal = 0;
    protected $itemCountPerPage = 12;
    protected $currentPage = 1;
    protected $pageCount = null;

    private function reset()
    {
        if ($this->pageCount) {
            $this->pageCount = ceil($this->itemCountTotal / $this->itemCountPerPage);
        }
        if ($this->currentPage > $this->pageCount) {
            $this->currentPage = $this->pageCount;
        }
        return true;
    }
    /**
     * Set list items count
     *
     * @param int $count
     */
    public function setTotalItemCount($count)
    {
        $this->itemCountTotal = (int) $count;
        return $this;
    }

    /**
     * Set pagesize number
     *
     * @param int $count
     */
    public function setItemCountPerPage($count)
    {

        if ($this->itemCountPerPage <= 0) {
             throw new \RuntimeException("Invalid param for pagesize number: $count");
            return false;
        }
        $this->itemCountPerPage = (int) $count;
        $this->reset();
        return $this;
    }

    /**
     * Set current page number
     *
     * @param int $number
     */
    public function setCurrentPageNumber($number)
    {
        $this->currentPage = (int)$number;

        if ($this->currentPage >= $this->getPageCount()) {
            $this->currentPage = $this->pageCount;
        }

        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }

        return $this;
    }

    /**
     * Get items count
     *
     * @return int
     */
    public function getTotalItemCount()
    {
        return $this->itemCountTotal;
    }

    /**
     * Get pagesize number
     *
     * @return int
     */
    public function getItemCountPerPage()
    {
        return $this->itemCountPerPage;
    }

    /**
     * Get page count
     *
     * @return int
     */
    public function getPageCount()
    {
        if (!$this->pageCount) {
            $this->pageCount = ceil($this->itemCountTotal / $this->itemCountPerPage);
        }

        return $this->pageCount;
    }

    /**
     * get current page number
     *
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->currentPage;
    }

    /**
     * get Previous page number
     *
     * @return int|null
     */
    public function getPreviousPageNumber()
    {
        if ( $this->currentPage > 1) {
            return $this->currentPage - 1;
        }
        return null;
    }

    /**
     * get next page number
     *
     * @return int|null
     */
    public function getNextPageNumber()
    {
        if ( $this->currentPage < $this->pageCount) {
            return $this->currentPage + 1;
        }
        return null;
    }

    /**
     * Get page range lists
     *
     * @param int $display how many page should display
     * @return array
     */
    public function getPageRange($display = 10)
    {
        $step = ceil($display / 2);
        if ($this->currentPage - $step > $this->pageCount - $display) {
            $startPage = $this->pageCount - $display + 1;
            $endPage = $this->pageCount;
            if ($startPage < 1) {
                $startPage = 1;
            }
        } else {
            if ($this->currentPage - $step < 0) {
                $step = $this->currentPage;
            }

            $offset     = $this->currentPage - $step;
            $startPage = $offset + 1;
            $endPage = $offset + $display;
            if ($endPage > $this->pageCount) {
                $endPage = $this->pageCount;
            }
        }

        $pageRange = [];
        if( $startPage > 1) {
             $pageRange[] = 1;
        }
        if( $startPage > 2) {
             $pageRange[] = null;
        }
        for ($number = $startPage; $number <= $endPage; $number++) {
            $pageRange[] = $number;
        }
        if( $endPage < $this->getPageCount() - 1) {
             $pageRange[] = null;
        }
        if( $endPage < $this->getPageCount()) {
             $pageRange[] = $this->getPageCount();
        }
        return $pageRange;
    }

    /**
     * Get first item from current page for SQL
     *
     * @return int
     */
    public function getLimitStart()
    {
        return ($this->getCurrentPageNumber() -1) * $this->itemCountPerPage;
    }
}