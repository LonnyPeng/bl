<?php
/**
 * Standard Library
 *
 * @package Framework_Controller
 * @subpackage Framework_Controller_Plugin
 */

namespace Framework\Controller\Plugin;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;

class Param implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocator
     */
    protected $locator = null;

    /**
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function __invoke($name = null, $default = null)
    {
        $params = $this->locator->get('Params');

        if (null === $name) {
            return $params;
        }
        return isset($params[$name]) ? $params[$name] : $default;
    }

    /**
     * Whether or not exist some parameter and value
     *
     * @param string $key
     * @param string|null $item
     * @return boolean
     */
    public function has($key, $item = null)
    {
        $params = $this->locator->get('Params');

        if (empty($params[$key])) {
            return false;
        }

        if (null == $item) {
            return true;
        }

        if (in_array($item, (array) $params[$key])) {
            return true;
        }

        return false;
    }

    /**
     * Deeply get a value by keys
     *
     * @param string|int $args
     * @return mixed It will return null if noset
     * @example
     *  $this->param->get('name')
     *  $this->param->get('filter', 'order', 'status', 0)
     */
    public function get($args)
    {
        $params = $this->locator->get('Params');
        $args = func_get_args();
        foreach ($args as $arg) {
            if (!isset($params[$arg])) {
                return null;
            }
            $params = $params[$arg];
        }
        return $params;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocator $serviceLocator
     * @return Controller
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