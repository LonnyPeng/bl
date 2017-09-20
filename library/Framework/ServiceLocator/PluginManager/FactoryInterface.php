<?php
/**
 * Standard Library
 *
 * @package Framework_ServiceLocator
 */

namespace Framework\ServiceLocator\PluginManager;

interface FactoryInterface
{
    /**
     * @return object
     */
    public function factory();
}