<?php

namespace App\Controller\Plugin;

class Exp
{
    protected $error = null;

    /**
     * Validate the expression
     * It will remove the whitespace of expression
     *
     * @param string $str
     * @return false|string
     */
    public function validate($str)
    {
        // remove whtespaces
        $str = preg_replace('/\s+/', '', $str);

        // empty string
        if ($str == '') {
            $this->error = null;
            return '';
        }

        // check special chars
        if (!preg_match("/^[\(\)\|\&\w\-\!]+$/", $str)) {
            $this->error = 'Special character was detected';
            return false;
        }

        // declare variables
        preg_match_all("/\(|\)|\&|\||\!|[\w|\-]+/", $str, $arr);
        $elems = $arr[0];
        $length = count($elems);
        $elem = $nextElem = null;
        $lbraces = 0;

        /**
         * check next character
         *
         *  OPERATOR    VALID_NEXT          INVALID_NEXT
         *  bgn     =>  \w, (, !, null      =>  ), &, |
         *  (       =>  \w, (, !            =>  ), &, |, null
         *  )       =>  &, |, ), null       =>  (, \w, !
         *  andor   =>  \w, (, !            =>  &, |, ), null
         *  !       => \w, (            =>  ), &, |, !, null
         *  \w      =>  ), &, |, null       =>  (, \w, !
         */
        for ($i = -1; $i < $length; $i++) {

            // current element, next element
            $elem = $i == -1 ? null : $elems[$i];
            $nextElem = $i + 1 == $length ? null : $elems[$i + 1];

            // match left and right braces
            if ($elem == '(') {
                $lbraces++;
            }
            elseif ($elem == ')') {
                $lbraces--;
                if ($lbraces < 0) {
                    $this->error = 'Braces cannot match each other';
                    return false;
                }
            }

            // check beginning
            if ($elem == null) {
                if (in_array($nextElem, array(')', '&', '|'))) {
                    $this->error = 'Invalid begging character';
                    return false;
                }
                continue;
            }

            // check "("
            if ($elem == '(') {
                if (in_array($nextElem, array(')', '&', '|', null))) {
                    $this->error = 'An error occurred after "' . $elem . '"';
                    return false;
                }
                continue;
            }

            // check ")"
            if ($elem == ')') {
                if (!in_array($nextElem, array('&', '|', ')', null))) {
                    $this->error = 'An error occurred after "' . $elem . '"';
                    return false;
                }
                continue;
            }

            // check "&", "|"
            if ($elem == '&' || $elem == '|') {
                if (in_array($nextElem, array('&', '|', ')', null))) {
                    $this->error = 'An error occurred after "' . $elem . '"';
                    return false;
                }
                continue;
            }

            // check "!"
            if ($elem == '!') {
                if (in_array($nextElem, array('&', '|', ')', '!', null))) {
                    $this->error = 'An error occurred after "' . $elem . '"';
                    return false;
                }
                continue;
            }

            // check "\w"
            if (!in_array($nextElem, array(')', '&', '|', null))) {
                $this->error = 'An error occurred after "' . $elem . '"';
                return false;
            }
        }

        // check left braces
        if ($lbraces) {
            $this->error = 'Braces cannot match each other';
            return false;
        }

        // OK
        $this->error = null;
        return $str;
    }

    /**
     * Create sql
     *
     * @param string $str
     * @param string $relatedField
     * @return boolean|string
     */
    public function getSql($str, $relatedField)
    {
        if (!$str) {
            return false;
        }
        $str = preg_replace('/[^\(|\)|\&|\||\!]+/i', "FIND_IN_SET('$0', $relatedField)", $str);
        $str = preg_replace(array('/\|/', '/&/'), array(' OR ', ' AND '), $str);

        return $str;
    }

    /**
     * Get error message
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
}