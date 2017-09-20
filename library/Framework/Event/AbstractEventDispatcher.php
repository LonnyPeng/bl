<?php
/**
 * Standard Library
 *
 * @package Framework_Event
 */

namespace Framework\Event;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;

/**
 * Event dispatcher abstract class
 */
abstract class AbstractEventDispatcher implements EventDispatcherInterface, ServiceLocatorAwareInterface
{
    /**
     * Its value is like the following:
     * <code>
     * array(
     *      event-type-1 => array(
     *          priority-1 => array(
     *              'callback-1', 'callback-2',
     *          ),
     *          priority-2 => array(
     *              'callback-3',
     *          ),
     *      ),
     *      event-type-2 => array(
     *          priority-3 => array(
     *              'callback-4',
     *          ),
     *      ),
     * );
     * </code>
     *
     * @var array
     */
    protected $listeners = array();

    /**
     * @var ServiceLocator|\Hints\ServiceLocator
     */
    protected $locator;

    /**
     * Add an event listener
     *
     * @param string $name
     * @param string|Closure $listener
     * @param int|string $priority (optional)
     * @return AbstractEventDispatcher
     */
    public function addEventListener($name, $listener, $priority = 0)
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = array();
        }

        if (!isset($this->listeners[$name][$priority])) {
            $this->listeners[$name][$priority] = array();
        }

        $this->listeners[$name][$priority][] = $listener;

        return $this;
    }

    /**
     * Remove an event listener
     *
     * @param string $name
     * @param string|Closure $listener (optional)
     * @return AbstractEventDispatcher
     */
    public function removeEventListener($name, $listener = null)
    {
        if (null === $listener) {
            unset($this->listeners[$name]);
            return $this;
        }

        if (isset($this->listeners[$name])) {
            foreach ($this->listeners[$name] as $priority => $listeners) {
                foreach ($listeners as $k => $item) {
                    if ($item == $listener) {
                        unset($this->listeners[$name][$priority][$k]);
                        return $this;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Dispatch an event
     *
     * @param EventInterface $event
     * @return AbstractEventDispatcher
     */
    public function dispatchEvent(EventInterface $event)
    {
        $name = $event->getName();

        // no listener
        if (!isset($this->listeners[$name])) {
            return $this;
        }

        // sort by priority
        krsort($this->listeners[$name]);

        // execute the callback
        foreach ($this->listeners[$name] as $listeners) {
            foreach ($listeners as $listener) {
                call_user_func_array($listener, func_get_args());
            }
        }

        // return
        return $this;
    }

    /**
     * Set a service locator
     *
     * @param ServiceLocator $serviceLocator
     * @return AbstractEventDispatcher
     */
    public function setServiceLocator(ServiceLocator $serviceLocator)
    {
        $this->locator = $serviceLocator;
        return $this;
    }

    /**
     * Get the service locator
     *
     * @return ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->locator;
    }
}