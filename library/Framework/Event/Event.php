<?php
/**
 * Standard Library
 *
 * @package Framework_Event
 */

namespace Framework\Event;

use ArrayAccess;
use InvalidArgumentException;

class Event implements EventInterface
{
    /**
     * @var string Event name
     */
    protected $name;

    /**
     * @var string|object The event target
     */
    protected $target;

    /**
     * @var array|ArrayAccess|object The event parameters
     */
    protected $params = array();

    /**
     * Constructor
     *
     * Accept a target and its parameters
     *
     * @param string $name (optional)
     * @param string|object $target (optional)
     * @param array|ArrayAccess|object $params (optional)
     */
    public function __construct($name = null, $target = null, $params = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $target) {
            $this->setTarget($target);
        }

        if (null !== $params) {
            $this->setParams($params);
        }
    }

    /**
     * Set the event name
     *
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Get the event name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set this event target
     *
     * @param string|object $target
     * @return Event
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Get the event target
     *
     * @return string|object
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set/overwrite parameters
     *
     * @param array|ArrayAccess|object $params
     * @return Event
     * @throws InvalidArgumentException
     */
    public function setParams($params)
    {
        if (!is_array($params) && !is_object($params)) {
            throw new InvalidArgumentException(sprintf(
                'Event parameters must be an array or object; received "%s%', gettype($params)
            ));
        }

        $this->params = $params;
        return $this;
    }

    /**
     * Get all parameters
     *
     * @return array|ArrayAccess|object
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set an individual parameter to a value
     *
     * @param string $name
     * @param mixed $value
     * @return Event
     */
    public function setParam($name, $value)
    {
        if (is_array($this->params) || $this->params instanceof ArrayAccess) {
            $this->params[$name] = $value;
        } else {
            $this->params->{$name} = $value;
        }
        return $this;
    }

    /**
     * Get an individual parameter
     *
     * @param string|int $name
     * @param mixed $default (optional)
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (is_array($this->params) || $this->params instanceof ArrayAccess) {
            if (!isset($this->params[$name])) {
                return $default;
            }
            return $this->params[$name];
        }

        // Check the normal objects
        if (!isset($this->params->{$name})) {
            return $default;
        }
        return $this->params->{$name};
    }
}