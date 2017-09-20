<?php
/**
 * Standard Library
 *
 * @package Framework_View
 * @subpackage Framework_View_Helper
 */

namespace Framework\View\Helper;

class Urlencode
{
    /**
     * @param string $str
     * @param string $raw
     * @return string
     */
    public function __invoke($str, $raw = true)
    {
        return $raw ? rawurlencode($str) : urlencode($str);
    }
}