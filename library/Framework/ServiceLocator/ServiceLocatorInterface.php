<?php
/**
 * Standard Library
 *
 * @package Framework_ServiceLocator
 */

namespace Framework\ServiceLocator;

interface ServiceLocatorInterface
{
    /**
     * Set a service
     *
     * @param string $name
     * @param mixed $value
     * @param boolean $readonly
     * @return ServiceLocator
     * @throws \InvalidArgumentException
     */
    public function setService($name, $value, $readonly = false);

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
    public function setInvokable($name, $invokable, $params = false, $readonly = false);

    /**
     * Get a service
     *
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($name);

    /**
     * Whether or not has a service.
     *
     * @param string $name
     * @return boolean
     */
    public function has($name);
}
