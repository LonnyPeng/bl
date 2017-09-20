<?php
/**
 * Standard Library
 *
 * @package Framework_ServiceLocator
 */

namespace Framework\ServiceLocator;

interface FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocator $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocator $serviceLocator);
}
