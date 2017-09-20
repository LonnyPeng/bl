<?php
/**
 * Standard Library
 *
 * @package Framework_Utils
 */

namespace Framework\Utils;

use Framework\Utils\FrameworkString;

class Url
{
    /**
     * add query to URL
     *
     * @param string|array $query
     * @param null|string $url
     * @return string
     * @example
     *      Url::buildUrl('id=3&type=test', '/example.php?id=1&name=test');
     *      Url::buildUrl(array('id' => 3, 'type' => 'test'), '/example.php?id=1&name=test');
     *      => /example.php?id=3&name=test&type=test
     */
    public static function buildUrl($query = null, $url = null)
    {
        // The default url is REQUEST_URI
        if (null === $url) {
            $url = $_SERVER['REQUEST_URI'];
        }

        // no new query
        if (!$query) {
            return $url;
        }

        // Get the position of query mark
        $markPos = strpos($url, '?');

        // Get the new query array
        if (is_string($query)) {
            parse_str($query, $newQueryArray);
        } else {
            $newQueryArray = $query;
        }

        // Merge the new query to the old query
        if (false === $markPos) {
            $path = $url;
        } else {
            $path = substr($url, 0, $markPos);
            parse_str(substr($url, $markPos + 1), $oldQueryArray);
            $newQueryArray = array_merge($oldQueryArray, $newQueryArray);
        }

        // concat the query to path
        return $path . String::concat(http_build_query($newQueryArray), '?');
    }
}
