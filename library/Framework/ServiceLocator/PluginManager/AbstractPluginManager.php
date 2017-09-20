<?php
/**
 * Standard Library
 *
 * @package Framework_ServiceLocator
 */

namespace Framework\ServiceLocator\PluginManager;

use Framework\ServiceLocator\InitializerInterface;
use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;

abstract class AbstractPluginManager implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocator|\Hints\ServiceLocator
     */
    protected $locator = null;

    /**
     * @var array
     */
    protected $plugins = array();

    /**
     * @var array
     */
    protected $callbacks = array();

    /**
     * @var AbstractPluginManager
     */
    protected static $instance = null;

    /**
     * Get the plugin class name
     *
     * @param string $name
     * @return string|boolean
     */
    abstract function getPluginClass($name);

    /**
     * Get the plugin instance
     *
     * @param string $name
     * @return object
     * @throws \InvalidArgumentException
     */
    public function get($name)
    {
        // already exist
        if (array_key_exists($name, $this->plugins)) {
            $plugin = $this->plugins[$name];

            // implement InitializerInterface
            if ($plugin instanceof InitializerInterface) {
                $plugin->initialize();
            }
            return $plugin;
        }

        // check callbacks
        if (array_key_exists($name, $this->callbacks)) {
            $plugin = $this->callbacks[$name];

            // Closure condition
            if (is_object($plugin) && $plugin instanceof \Closure) {
                $plugin = call_user_func($plugin);
            }
        }
        // check plugin
        else {
            // get the plugin class name
            $class = $this->getPluginClass($name);

            // The plugin does not exist
            if (!$class) {
                throw new \InvalidArgumentException("Invalid plugin name: $name");
                return false;
            }

            // instance
            /* @var $instance Plugin\AbstractPlugin */
            $plugin = new $class;
        }

        // set service locator
        if ($plugin instanceof ServiceLocatorAwareInterface) {
            $plugin->setServiceLocator($this->locator);
        }

        // factory
        if ($plugin instanceof FactoryInterface) {
            $plugin = $plugin->factory();
        }

        // save
        $this->plugins[$name] = $plugin;

        // return
        return $plugin;
    }

    /**
     * Register a helper function
     *
     * @param string $name
     * @param callback $callback
     * @return HelperManager
     */
    public function register($name, $callback)
    {
        $this->callbacks[$name] = $callback;
        return $this;
    }

    /**
     * Unregister a helper function
     *
     * @param string $name
     * @return HelperManager
     */
    public function unregister($name)
    {
        unset($this->callbacks[$name]);
        return $this;
    }

    /**
     * Whether or not exist the plugin name
     *
     * @param string $name
     * @return boolean
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->callbacks) || (bool) $this->getPluginClass($name);
    }

    /**
     * Get the plugin instance
     *
     * @param string $name
     * @return object
     * @throws \InvalidArgumentException
     * @see AbstractPluginManager::get($name)
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Overloading: proxy to plugins
     *
     * If the plugin does not define __invoke, it will be return.
     * If the plugin does define __invoke, it will be called as a functor.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $plugin = $this->get($name);

        // callable
        if (is_callable($plugin)) {
            return call_user_func_array($plugin, $arguments);
        }

        // return
        return $plugin;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocator $serviceLocator
     * @return AbstractPluginManager
     */
    public function setServiceLocator(ServiceLocator $serviceLocator)
    {
        $this->locator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->locator;
    }
}