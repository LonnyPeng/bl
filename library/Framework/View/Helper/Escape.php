<?php
/**
 * Standard Library
 *
 * @package Framework_View
 * @subpackage Framework_View_Helper
 */

namespace Framework\View\Helper;

class Escape
{
    /**
     * Escape the html string
     *
     * @param string $str
     * @return string
     */
    public function __invoke($str)
    {
        return htmlspecialchars($str, ENT_COMPAT | ENT_XHTML);
    }
}