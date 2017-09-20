<?php
/**
 * Standard Library
 *
 * @package Framework_ServiceLocator
 */

namespace Framework\ServiceLocator;

interface ServiceLocatorAwareInterface
{
    /**
     * Set service locator
     *
     * @param ServiceLocator $serviceLocator
     */
    public function setServiceLocator(ServiceLocator $serviceLocator);

    /**
     * Get service locator
     *
     * @return ServiceLocator
     */
    public function getServiceLocator();
}

