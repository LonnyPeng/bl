<?php
/**
 * Standard Library
 *
 * @package Framework_View
 */

namespace Framework\View;

class Variables extends \ArrayObject
{
    /**
     * Assign many values at once
     *
     * @param  array|object $spec
     * @return Variables
     * @throws \InvalidArgumentException
     */
    public function assign($spec)
    {
        if (is_object($spec)) {
            if (method_exists($spec, 'toArray')) {
                $spec = $spec->toArray();
            } else {
                $spec = (array) $spec;
            }
        }

        if (!is_array($spec)) {
            throw new \InvalidArgumentException(sprintf(
                'assign() expects either an array or an object as an argument; received "%s"',
                gettype($spec)
            ));
        }

        foreach ($spec as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the variable value
     *
     * If the value has not been defined, a null value will be returned
     *
     * Otherwise, returns _escaped_ version of the value.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (!$this->offsetExists($key)) {
            return null;
        }

        $return = parent::offsetGet($key);

        // If we have a closure/functor, invoke it, and return its return value
        if (is_object($return) && is_callable($return)) {
            $return = call_user_func($return);
        }

        return $return;
    }

    /**
     * Clear all variables
     *
     * @return void
     */
    public function clear()
    {
        $this->exchangeArray(array());
    }
}
