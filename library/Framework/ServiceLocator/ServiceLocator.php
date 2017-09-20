<?php
/**
 * Standard Library
 *
 * @package Framework_ServiceLocator
 */

namespace Framework\ServiceLocator;

use Framework\Utils\Reflection;

/**
 * @use ServiceLocatorAwareInterface
 * @use FactoryInterface
 * @property \Framework\Db\Pdo $db The DB instance
 * @property \Framework\Router\Router $router The router instance
 * @property \Framework\Controller\FrontController $frontController The front controller
 * @property \Framework\Controller\AbstractActionController $controller The current controller instance
 * @property string $controllerName The controller name
 * @property string $actionName The action name
 */
class ServiceLocator implements ServiceLocatorInterface
{
    /**
     * @var array
     */
    protected $invokables = array();

    /**
     * @var array
     */
    protected $services = array();

    /**
     * @var array
     */
    protected $aliases = array();

    /**
     * @var array
     */
    protected $readonly = array();

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * Constructor
     *
     * @param array|ArrayAccess $config
     */
    public function __construct($config = array())
    {
        if (!$config) {
            return;
        }

        // install invokables
        if (isset($config['invokables'])) {
            foreach ($config['invokables'] as $name => $invokable) {
                $this->setInvokable($name, $invokable, false);
            }
        }

        // install services
        if (isset($config['services'])) {
            foreach ($config['services'] as $name => $value) {
                $this->setService($name, $value, false);
            }
        }

        // install aliases
        if (isset($config['aliases'])) {
            foreach ($config['aliases'] as $name => $alias) {
                $this->setAlias($name, $alias);
            }
        }

        // set readonly
        if (isset($config['readonly'])) {
            foreach ($config['readonly'] as $name => $readonly) {
                $canonicalName = $this->getCanonicalName($name);
                if ($canonicalName) {
                    $this->readonly[$canonicalName] = (bool) $readonly;
                }
            }
        }

        // set parameters
        if (isset($config['parameters'])) {
            foreach ($config['parameters'] as $name => $params) {
                $canonicalName = $this->getCanonicalName($name);
                if (isset($this->invokables[$canonicalName]) && is_array($params)) {
                    $this->parameters[$name] = $params;
                }
            }
        }
    }

    /**
     * Set a service
     *
     * @param string $name
     * @param mixed $value
     * @param boolean $readonly
     * @return ServiceLocator
     * @throws \InvalidArgumentException
     */
    public function setService($name, $value, $readonly = false)
    {
        if ($this->isReadonly($name)) {
            throw new \InvalidArgumentException(sprintf(
                'A service by the name or alias "%s" already exists and cannot be overridden; please use an alternate name', $name
            ));
            return $this;
        }

        $this->services[$name] = $value;
        $this->readonly[$name] = (bool) $readonly;
        return $this;
    }

    /**
     * Set an invokable class or callback
     *
     * @param string $name
     * @param string|callback $invokable
     * @param array|boolean $params
     * @param boolean $readonly
     * @return ServiceLocator
     * @throws \InvalidArgumentException
     * @example
     *  $this->setInvokable('db', 'Framework\Db\Pdo', array('dsn' => 'xxx', 'username' => 'xxx', 'password' => 'xxx'));
     *  $this->setInvokable('db', 'App\Db', true);
     */
    public function setInvokable($name, $invokable, $params = false, $readonly = false)
    {
        if ($this->isReadonly($name)) {
            throw new \InvalidArgumentException(sprintf(
                'A service by the name or alias "%s" already exists and cannot be overridden; please use an alternate name', $name
            ));
            return $this;
        }

        $this->invokables[$name] = $invokable;

        if (is_array($params)) {
            $this->parameters[$name] = $params;
            if ($readonly) {
                $this->readonly[$name] = true;
            }
        }
        elseif ($params) {
            $this->readonly[$name] = true;
        }
        return $this;
    }

    /**
     * Set an alias of server
     *
     * @param string $name
     * @param string $alias
     * @return ServiceLocator
     */
    public function setAlias($name, $alias)
    {
        $canonicalName = $this->getCanonicalName($alias);
        if ($canonicalName) {
            $this->aliases[$name] = $alias;
        }
        return $this;
    }

    /**
     * @param string $name
     * @return boolean It also returns false if doesn't exist.
     */
    public function isReadonly($name)
    {
        $canonicalName = $this->getCanonicalName($name);
        return $canonicalName && !empty($this->readonly[$canonicalName]);
    }

    /**
     * Get a service
     *
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($name)
    {
        // get the canonical name
        $canonicalName = $this->getCanonicalName($name);

        // search from services
        if (array_key_exists($canonicalName, $this->services)) {
            return $this->services[$canonicalName];
        }

        // search from invokables
        if (isset($this->invokables[$canonicalName])) {
            $invokable = $this->invokables[$canonicalName];

            // It is an invokable class
            if (is_string($invokable) && class_exists($invokable)) {

                $params = $this->getInvokableParams($canonicalName);
                $instance = $params ? Reflection::newInstance($invokable, $params) : new $invokable;

                /** @see ServiceLocatorAwareInterface */
                if ($instance instanceof ServiceLocatorAwareInterface) {
                    $instance->setServiceLocator($this);
                }
                /** @see FactoryInterface */
                elseif ($instance instanceof FactoryInterface) {
                    $instance = $instance->createService($this);
                }

                $this->services[$canonicalName] = $instance;
                return $instance;
            }
            // It is a callback function
            elseif (is_callable($invokable)) {
                $instance = $invokable($this, $canonicalName, $name);
                $this->services[$canonicalName] = $instance;
                return $instance;
            }
        }

        // directly invoke if exists
        if (class_exists($name)) {
            $this->setInvokable($name, $name);
            return $this->get($name);
        }

        // throw exception
        throw new \InvalidArgumentException(sprintf(
            'Attempt to get an invalid service: %s', $name
        ));
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException
     * @see ServiceLocator::get($name)
     */
    public function __get($name)
    {
        return $this->get(ucfirst($name));
    }

    /**
     * Whether or not has a service.
     *
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        if (
            array_key_exists($name, $this->services)
            || isset($this->invokables[$name])
            || isset($this->aliases[$name])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get the canonical name
     *
     * @param string $name
     * @return string|null
     */
    public function getCanonicalName($name)
    {
        while (isset($this->aliases[$name])) {
            $name = $this->aliases[$name];
        }

        if (array_key_exists($name, $this->services) || isset($this->invokables[$name])) {
            return $name;
        }

        return null;
    }

    /**
     * Get the params of invokable class/callback
     *
     * @param string $name
     * @return array
     */
    protected function getInvokableParams($name)
    {
        $canonicalName = $this->getCanonicalName($name);
        return isset($this->parameters[$canonicalName]) ? $this->parameters[$canonicalName] : array();
    }
}
