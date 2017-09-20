<?php
/**
 * Standard Library
 *
 * @package Framework_Utils
 */

namespace Framework\Utils;

/**
 * @example
 *  <code>
 *  foreach(new Combinations(array('a', 'b', 'c', 'd', 'e', 'f', 'g'), 5) as $arr) {
 *      echo implode('', $arr), ' ';
 *  }
 *  </code>
 */
class Combinations implements \Iterator
{
    protected $count = null;
    protected $elements = null;
    protected $position = 0;
    protected $pointers = array();
    protected $k = 0;

    public function __construct($elements, $k)
    {
        $this->elements = array_values($elements);
        $this->count = count($this->elements);
        $this->k = $k;
        $this->rewind();
    }

    public function key()
    {
        return $this->position;
    }

    public function current()
    {
        $r = array();
        for($i = 0; $i < $this->k; $i++) {
            $r[] = $this->elements[$this->pointers[$i]];
        }
        return $r;
    }

    public function next()
    {
        if ($this->_next()) {
            $this->position++;
        } else {
            $this->position = -1;
        }
    }

    public function rewind()
    {
        $this->pointers = range(0, $this->k);
        $this->position = 0;
    }

    public function valid()
    {
        return $this->position >= 0;
    }

    protected function _next()
    {
        $i = $this->k - 1;
        while ($i >= 0 && $this->pointers[$i] == $this->count - $this->k + $i) {
            $i--;
        }
        if ($i < 0) {
            return false;
        }

        $this->pointers[$i]++;
        while ($i++ < $this->k - 1) {
            $this->pointers[$i] = $this->pointers[$i - 1] + 1;
        }
        return true;
    }
}