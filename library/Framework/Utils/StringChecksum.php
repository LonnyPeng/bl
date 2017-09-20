<?php
/**
 * Standard Library
 *
 * @package Framework_Utils
 */

namespace Framework\Utils;

class StringChecksum
{
    /**
     * @var string
     */
    protected $chars = 'ABCDEFGHIJKLMNPQRSTUVWXYZ23456789';

    /**
     * @var int
     */
    private $count = null;

    /**
     * Contructor
     *
     * @param string $chars (optional)
     */
    public function __construct($chars = null)
    {
        if ($chars) {
            $this->chars = $chars;
        }
        $this->count = strlen($this->chars);
    }

    /**
     * Get the last verification character by given string
     *
     * @param string $str
     * @return char
     */
    public function getVericationChar($str)
    {
        $str = strtoupper($str);
        $md5str = md5($str);
        $mod = hexdec(substr($md5str, 0, 3)) % $this->count;
        return $this->chars{$mod};
    }

    /**
     * Verify string
     *
     * @param string $str
     * @return boolean
     */
    public function verify($str)
    {
        $str = strtoupper($str);
        $strlen = strlen($str);

        if ($strlen < 2) {
            return false;
        }
        if (!preg_match('/^[' . $this->chars . ']+$/', $str)) {
            return false;
        }

        $char = $this->getVericationChar(substr($str, 0, $strlen - 1));
        return $char === $str{$strlen - 1};
    }

    /**
     * Generate checksum string
     *
     * @param int $length
     * @param string $prefix (optional)
     * @param boolean $yearmonth (optional)
     */
    public function generate($length, $prefix = null, $yearmonth = false)
    {
        $str = '';

        if ($prefix) {
            $str .= $prefix;
        }

        if ($yearmonth) {
            $year = $this->chars{intval(date('y')) % $this->count};
            $month = $this->chars{intval(date('m')) % $this->count};
            $str .= $year . $month;
        }

        $length = $length - strlen($str) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $this->chars{rand(0, $this->count - 1)};
        }

        $last = $this->getVericationChar($str);
        $str .= $last;
        return $str;
    }
}

