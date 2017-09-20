<?php
/**
 * Standard Library
 *
 * @package Framework_Console
 */

namespace Framework\Console;

class ArgumentParser
{
    /**
     * @param array $args
     * @return array|false
     * @throws \InvalidArgumentException
     */
    public static function parse(array $args)
    {
        // It will save all the parameters
        $params = array();

        // remove filename
        array_shift($args);

        // controller/action
        if (isset($args[0]) && substr($args[0], 0, 1) !== '-') {
            $path = array_shift($args);
            $identifiers = explode('/', $path);
            if (count($identifiers) !== 2) {
                throw new \InvalidArgumentException("Invalid path (controller/action)");
                return false;
            }

            // save _controller & _action
            $params['_controller'] = $identifiers[0];
            $params['_action'] = $identifiers[1];
        }

        // save other parameters
        foreach ($args as $arg) {
            $arg = ltrim($arg, '-');
            $arr = explode('=', $arg, 2);

            // replace line-through to underline
            $name = str_replace('-', '_', $arr[0]);

            // no value
            if (count($arr) == 1) {
                $params[$name] = true;
            }
            // own value
            else {
                $value = $arr[1];
                $lower = strtolower($value);

                // true
                if (in_array($lower, array('1', 'true', 'yes', 'y'))) {
                    $params[$name] = true;
                }
                // false
                elseif (in_array($lower, array('0', 'false', 'no', 'n'))) {
                    $params[$name] = false;
                }
                // assign value
                else {
                    $params[$name] = $value;
                }
            }
        }

        // return
        return $params;
    }
}