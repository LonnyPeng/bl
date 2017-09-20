<?php
/**
 * Standard Library
 *
 * @package Framework\Utils
 */

namespace Framework\Utils;

class WordConvertor
{
    /**
     * @param string $str
     * @param boolean $tolower
     * @return string
     */
    public static function camelCaseToDash($str, $tolower = false)
    {
        return self::camelCaseToSeparator($str, '-', $tolower);
    }

    /**
     * @param string $str
     * @param boolean $tolower
     * @return string
     */
    public static function camelCaseToUnderscore($str, $tolower = false)
    {
        return self::camelCaseToSeparator($str, '_', $tolower);
    }

    /**
     * @param string $str
     * @param boolean $lowerFirst
     * @return string
     */
    public static function dashToCamelCase($str, $lowerFirst = false)
    {
        return self::separatorToCamelCase($str, '-', $lowerFirst);
    }

    /**
     * @param string $str
     * @param boolean $lowerFirst
     * @return string
     */
    public static function underscoreToCamelCase($str, $lowerFirst = false)
    {
        return self::separatorToCamelCase($str, '_', $lowerFirst);
    }

    /**
     * @param string $str
     * @param char $separator
     * @param boolean $tolower
     * @return string
     */
    public static function camelCaseToSeparator($str, $separator, $tolower = false)
    {
        $str = ltrim(preg_replace("/\p{Lu}/", $separator . '${0}', $str), $separator);
        if ($tolower) {
            $str = strtolower($str);
        }
        return $str;
    }

    /**
     * @param string $str
     * @param char|char[] $separator
     * @return string
     */
    public static function separatorToCamelCase($str, $separator = array('_', '_'), $lowerFirst = false)
    {
        $str = str_replace($separator, ' ', $str);
        $str = preg_replace('/\s+/', '', ucwords($str));
        if ($lowerFirst) {
            $str{0} = strtolower($str{0});
        }
        return $str;
    }
}