<?php
/**
 * Standard Library
 *
 * @package Framework_View
 * @subpacage Framework_View_Helper
 */

namespace Framework\View\Helper;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;
use Framework\ServiceLocator\InitializerInterface;
use Framework\Router\Router;
use Framework\Utils\Http;
use Framework\Utils\FrameworkString;

class Url implements ServiceLocatorAwareInterface, InitializerInterface
{
    /**
     * @var ServiceLocator
     */
    protected $locator = null;

    /**
     * @var Router
     */
    protected $router = null;

    /**
     * @var string
     */
    private $path = null;

    /**
     * @var array
     */
    private $params = null;

    /**
     * @var boolean
     */
    private $https = false;

    /**
     * @var boolean
     */
    private $forceHost = false;

    /**
     * Initialize the URL
     *
     * @param string $path
     * @param array $params
     * @param boolean $https
     * @param boolean $forceHost
     * @return Url
     */
    public function __invoke($path = 'default/index', $params = null, $https = false, $forceHost = false)
    {
        $this->path = $path;
        $this->params = (array) $params;
        $this->https = $https;
        $this->forceHost = $forceHost;
        return $this;
    }

    /**
     * Reset the object
     *
     * @return Url
     */
    public function initialize()
    {
        $this->path = null;
        $this->params = null;
        $this->https = false;
        $this->forceHost = false;
        return $this;
    }

    /**
     * Set action path
     *
     * @param string $path
     * @return Url
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get action path
     *
     * @return string
     */
    public function getPath()
    {
        if (null === $this->path) {
            // controller mode
            if ($this->locator->has('Controller')) {
                $params = $this->locator->get('Params');
                $this->setPath($params['_controller'] . '/' . $params['_action']);
            }
            // not controller mode
            else {
                $this->setPath(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
            }
        }
        return $this->path;
    }

    /**
     * Set HTTPS
     *
     * @param boolean $is
     * @return Url
     */
    public function setHttps($is)
    {
        $this->https = $is;
        return $this;
    }

    /**
     * Include Host
     *
     * @param boolean $force
     * @return Url
     */
    public function forceHost($force)
    {
        $this->forceHost = $force;
        return $this;
    }

    /**
     * Only keep the necessary keys of parameter
     *
     * @param array|string $keys
     * @return Url
     * @see Url::unkeep()
     * @example
     *  $this->url->keep('page', 'pagesize');
     *  $this->url->keep(array('page', 'pagesize'));
     *  $this->url->keep('products', 'filters' => 'status');
     *  $this->url->keep('products', 'filters' => array('status'));
     */
    public function keep($keys)
    {
        $this->getParams();

        if (!is_array($keys)) {
            $keys = func_get_args();
        }

        $this->params = $this->_keep($this->params, $keys);
        return $this;
    }

    /**
     * It is a callback function for <em>Url::keep()</em>
     *
     * @param array|string $params
     * @param array|string $keys
     * @return array
     */
    protected function _keep($params, $keys)
    {
        $retval = array();
        foreach ((array) $keys as $key => $value) {
            if (is_numeric($key)) {
                if (isset($params[$value])) {
                    $retval[$value] = $params[$value];
                }
            } else {
                if (isset($params[$key])) {
                    $retval[$key] = $this->_keep($params[$key], $value);
                }
            }
        }
        return $retval;
    }

    /**
     * Remove the unnecessary keys of parameter
     *
     * @param array|string $keys
     * @return Url
     * @see Url::keep()
     * @example
     *  $this->url->unkeep('page', 'pagesize')
     *  $this->url->unkeep(array('page', 'pagesize'))
     *  $this->url->unkeep('products', 'filters' => 'status');
     *  $this->url->unkeep('products', 'filters' => array('status'));
     */
    public function unkeep($keys)
    {
        $this->getParams();

        if (!is_array($keys)) {
            $keys = func_get_args();
        }

        $this->_unkeep($this->params, $keys);
        return $this;
    }

    /**
     * It is a callback function for <em>Url::unkeep()</em>
     *
     * @param array|string $params
     * @param array|string $keys
     */
    protected function _unkeep(&$params, $keys)
    {
        foreach ((array) $keys as $key => $value) {
            if (is_numeric($key)) {
                if (isset($params[$value])) {
                    unset($params[$value]);
                }
            } else {
                if (isset($params[$key])) {
                    $this->_unkeep($params[$key], $value);
                }
            }
        }
    }

    /**
     * Add elements to parameters
     *
     * @param array $elements
     * @return Url
     * @see Url::remove()
     * @example
     *  $this->url->add(array('status' => 9)));
     *  $this->url->add(array('filters' => array('status' => 3)));
     *  $this->url->add(array('filters' => array('status' => [3])));
     */
    public function add($elements)
    {
        $this->getParams();
        $this->_add($this->params, $elements);
        return $this;
    }

    /**
     * It is a callback function for <em>Url::add()</em>
     *
     * @param array $params
     * @param array|string $elements
     */
    protected function _add(&$params, $elements)
    {
        foreach ((array) $elements as $key => $element) {
            if (is_numeric($key)) {
                if (false === array_search($element, $params)) {
                    $params[] = $element;
                }
            } else {
                if (isset($params[$key])) {
                    $params[$key] = (array) $params[$key];
                    $this->_add($params[$key], $element);
                } else {
                    $params[$key] = $element;
                }
            }
        }
    }

    /**
     * Remove elements from parameters
     *
     * @param array $elements
     * @return Url
     * @see Url::add()
     * @example
     *  $this->url->remove(array('status' => 9)));
     *  $this->url->remove(array('filters' => array('status' => 3)));
     *  $this->url->remove(array('filters' => array('status' => [3])));
     */
    public function remove($elements)
    {
        $this->getParams();
        $this->_remove($this->params, $elements);
        return $this;
    }

    /**
     * It is a callback function for <em>Url::remove()</em>
     *
     * @param array $params
     * @param array|string $elements
     */
    protected function _remove(&$params, $elements)
    {
        foreach ((array) $elements as $key => $element) {
            if (is_numeric($key)) {
                $pos = array_search($element, $params);
                if (false !== $pos) {
                    array_splice($params, $pos, 1);
                }
            } else {
                if (isset($params[$key])) {
                    $params[$key] = (array) $params[$key];
                    $this->_remove($params[$key], $element);
                }
            }
        }
    }

    /**
     * Whether or not has the element
     *
     * @param string $arg
     * @param string $element
     * @return boolean
     * @see Url::toggle()
     * @example
     *  $this->url->has('name', 'bruce');
     *  $this->url->has('filter', 'status', 'status', 4);
     */
    public function has($arg, $element)
    {
        $this->getParams();

        $args = func_get_args();
        $element = array_pop($args);

        $params = &$this->params;
        foreach ($args as $arg) {
            if (!isset($params[$arg])) {
                return false;
            }

            $params = &$params[$arg];
            $params = (array) $params;
        }

        if (false === array_search($element, $params)) {
            return false;
        }
        return true;
    }

    /**
     * Toggle an element
     *
     * @param string $arg
     * @param string $element
     * @return Url
     * @see Url::has()
     * @example
     *  $this->url->toggle('name', 'bruce');
     *  $this->url->toggle('filter', 'status', 'status', 4);
     */
    public function toggle($arg, $element)
    {
        $this->getParams();

        $args = func_get_args();
        $element = array_pop($args);

        $params = &$this->params;
        foreach ($args as $arg) {
            $params = &$params[$arg];
            $params = (array) $params;
        }

        $pos = array_search($element, $params);
        if (false === $pos) {
            $params[] = $element;
        } else {
            array_splice($params, $pos, 1);
        }

        return $this;
    }

    /**
     * Add parameters (only merge to the origin parameters)
     *
     * @param array $params
     * @return Url
     * @deprecated
     */
    public function addParams(array $params)
    {
        $this->getParams();

        foreach ($params as $key => $value) {
            if (
                isset($this->params[$key])
                && (is_array($this->params[$key]) || is_array($value))
            ) {
                $this->params[$key] = array_unique(array_merge((array) $this->params[$key], (array) $value));
            } else {
                $this->params[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Toggle an array value
     *
     * @param string $arrKey
     * @param string $value
     * @return Url
     * @deprecated
     */
    public function toggleParam($arrKey, $value)
    {
        $this->getParams();

        if (isset($this->params[$arrKey]) && in_array($value, (array) $this->params[$arrKey])) {
            return $this->removeParams(array($arrKey => $value));
        }
        return $this->addParams(array($arrKey => $value));
    }

    /**
     * Remove some parameters
     *
     * @param array|string $array
     * @return Url
     * @deprecated
     */
    public function removeParams($array)
    {
        $this->getParams();

        // diff
        foreach ((array) $array as $key => $item) {
            if (is_numeric($key)) {
                unset($this->params[$item]);
            } elseif (array_key_exists($key, $this->params)) {
                $this->params[$key] = array_diff((array) $this->params[$key], (array) $item);
            }
        }

        return $this;
    }

    /**
     * Keep some parameters
     *
     * @param array|string $array
     * @return Url
     * @deprecated
     */
    public function keepParams($array)
    {
        $this->getParams();

        // remove the unnecessary parameters
        $array = (array) $array;
        foreach ($this->params as $key => $item) {
            if (!in_array($key, $array)) {
                unset($this->params[$key]);
            }
        }

        return $this;
    }

    /**
     * Sort some key
     *
     * @param string $key
     * @param string $helper
     * @param mixed $more
     * @return Url
     */
    public function sort($key, $helper)
    {
        if (isset($this->params[$key])) {
            $args = array_slice(func_get_args(), 2);
            array_unshift($args, (array) $this->params[$key]);
            /* @var $helpers \Framework\View\HelperManager */
            $helpers = $this->locator->get('Framework\View\HelperManager');
            $this->params[$key] = call_user_func_array(array($helpers, $helper), $args);
        }
        return $this;
    }

    /**
     * Create URL
     *
     * @return string
     */
    public function __toString()
    {
        // Path
        $path = $this->getPath();

        // Params
        $params = $this->getParams();

        // Not controller mode
        $includeHost = strpos($path, '//') !== false;
        if ($includeHost || strpos($path, '.') !== false) {
            $query = http_build_query($params);
            $url = $path . String::concat($query, '?');

            if ($includeHost) {
                return $url;
            }
            if ($path{0} != '/') {
                $url = BASE_PATH . $url;
            }
            if (Http::isHttps() === $this->https && false === $this->forceHost) {
                return $url;
            }

            if ($this->https) {
                $host = defined('HTTPS_SERVER') ? HTTPS_SERVER : 'https://' . $_SERVER['HTTP_HOST'];
            } else {
                $host = defined('HTTP_SERVER') ? HTTP_SERVER : 'http://' . $_SERVER['HTTP_HOST'];
            }
            return $host . $url;
        }

        // crerate url
        $url = $this->router->createUrl($path, $params);
		if (Http::isHttps() === $this->https && false === $this->forceHost) {
			return $url;
		}
        if ($this->https) {
            $host = defined('HTTPS_SERVER') ? HTTPS_SERVER : 'https://' . $_SERVER['HTTP_HOST'];
        } else {
            $host = defined('HTTP_SERVER') ? HTTP_SERVER : 'http://' . $_SERVER['HTTP_HOST'];
        }
        return $host . $url;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocator $locator
     * @return Url
     */
    public function setServiceLocator(ServiceLocator $locator)
    {
        $this->locator = $locator;
        $this->router = $this->locator->get('Router');
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

    /**
     * Get all the parameters
     *
     * @return array
     */
    protected function getParams()
    {
        if (null === $this->params) {
            $this->params = $this->locator->get('Params');
            unset($this->params['_controller'], $this->params['_action']);
        }
        return $this->params;
    }
}