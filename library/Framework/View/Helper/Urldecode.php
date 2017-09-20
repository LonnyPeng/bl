<?php
/**
 * Standard Library
 *
 * @package Framework_View
 * @subpackage Framework_View_Helper
 */

namespace Framework\View\Helper;

class Urldecode
{
    /**
     * @param string $str
     * @param boolean $raw
     * @return string
     */
    public function __invoke($str, $raw = true)
    {
        return $raw ? rawurldecode($str) : urldecode($str);
    }
}