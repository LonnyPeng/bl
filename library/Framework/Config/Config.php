<?php
/**
 * Standard Library
 *
 * @package Framework_Config
 * @see Zend_Config
 */

namespace Framework\Config;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Provides a property based interface to an array.
 * The data are read-only unless $readonly is set to false
 * on construction.
 *
 * Implements Countable, Iterator and ArrayAccess
 * to facilitate easy access to the data.
 */
class Config implements ArrayAccess, Countable, Iterator
{
    /**
     * Whether modifications to configuration data are allowed.
     *
     * @var boolean
     */
    protected $readonly = false;

    /**
     * Number of elements in configuration data.
     *
     * @var integer
     */
    protected $count = 0;

    /**
     * Data withing the configuration.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Used when unsetting values during iteration to ensure we do not skip
     * the next element.
     *
     * @var boolean
     */
    protected $skipNextIteration;

    /**
     * Constructor.
     *
     * Data is read-only unless $readonly is set to false
     * on construction.
     *
     * @param array $array
     * @param boolean $readonly
     */
    public function __construct(array $array, $readonly = false)
    {
        $this->readonly = (boolean) $readonly;

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->data[$key] = new self($value, $this->readonly);
            } else {
                $this->data[$key] = $value;
            }

            $this->count++;
        }
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return $default;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Set a value in the config.
     *
     * Only allow setting of a property if $readonly  was set to false
     * on construction. Otherwise, throw an exception.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws \RuntimeException
     */
    public function __set($name, $value)
    {
        if (!$this->readonly) {

            if (is_array($value)) {
                $value = new self($value, true);
            }

            if (null === $name) {
                $this->data[] = $value;
            } else {
                $this->data[$name] = $value;
            }

            $this->count++;
        } else {
            throw new \RuntimeException('Config is readonly');
        }
    }

    /**
     * Deep clone of this instance to ensure that nested Framework\Config are also cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $array = array();

        foreach ($this->data as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = clone $value;
            } else {
                $array[$key] = $value;
            }
        }

        $this->data = $array;
    }

    /**
     * Return an associative array of the stored data.
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $data  = $this->data;

        /** @var self $value */
        foreach ($data as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * isset() overloading
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * unset() overloading
     *
     * @param  string $name
     * @return void
     * @throws \InvalidArgumentException
     */
    public function __unset($name)
    {
        if ($this->readonly) {
            throw new \InvalidArgumentException('Config is read only');
        } elseif (isset($this->data[$name])) {
            unset($this->data[$name]);
            $this->count--;
            $this->skipNextIteration = true;
        }
    }

    /**
     * count(): defined by Countable interface.
     *
     * @see Countable::count()
     * @return integer
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * current(): defined by Iterator interface.
     *
     * @see Iterator::current()
     * @return mixed
     */
    public function current()
    {
        $this->skipNextIteration = false;
        return current($this->data);
    }

    /**
     * key(): defined by Iterator interface.
     *
     * @see Iterator::key()
     * @return mixed
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * next(): defined by Iterator interface.
     *
     * @see Iterator::next()
     * @return void
     */
    public function next()
    {
        if ($this->skipNextIteration) {
            $this->skipNextIteration = false;
            return;
        }

        next($this->data);
    }

    /**
     * rewind(): defined by Iterator interface.
     *
     * @see Iterator::rewind()
     * @return void
     */
    public function rewind()
    {
        $this->skipNextIteration = false;
        reset($this->data);
    }

    /**
     * valid(): defined by Iterator interface.
     *
     * @see Iterator::valid()
     * @return boolean
     */
    public function valid()
    {
        return ($this->key() !== null);
    }

    /**
     * offsetExists(): defined by ArrayAccess interface.
     *
     * @see ArrayAccess::offsetExists()
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * offsetGet(): defined by ArrayAccess interface.
     *
     * @see ArrayAccess::offsetGet()
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * offsetSet(): defined by ArrayAccess interface.
     *
     * @see ArrayAccess::offsetSet()
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * offsetUnset(): defined by ArrayAccess interface.
     *
     * @see ArrayAccess::offsetUnset()
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * Merge another Config with this one.
     *
     * For duplicate keys, the following will be performed:
     * - Nested Configs will be recursively merged.
     * - Items in $merge with INTEGER keys will be appended.
     * - Items in $merge with STRING keys will overwrite current values.
     *
     * @param Config $merge
     * @return Config
     */
    public function merge(self $merge)
    {
        /** @var Config $value */
        foreach ($merge as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                if (is_int($key)) {
                    $this->data[] = $value;
                } elseif ($value instanceof self && $this->data[$key] instanceof self) {
                    $this->data[$key]->merge($value);
                } else {
                    if ($value instanceof self) {
                        $this->data[$key] = new self($value->toArray(), $this->readonly);
                    } else {
                        $this->data[$key] = $value;
                    }
                }
            } else {
                if ($value instanceof self) {
                    $this->data[$key] = new self($value->toArray(), $this->readonly);
                } else {
                    $this->data[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Prevent any more modifications being made to this instance.
     *
     * Useful after merge() has been used to merge multiple Config objects
     * into one object which should then not be modified again.
     *
     * @return void
     */
    public function setReadonly()
    {
        $this->readonly = true;

        /** @var Config $value */
        foreach ($this->data as $value) {
            if ($value instanceof self) {
                $value->setReadonly();
            }
        }
    }

    /**
     * Returns whether this Config object is read only or not.
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->readonly;
    }
}
