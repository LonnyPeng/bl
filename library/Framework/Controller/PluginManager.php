<?php
/**
 * Standard Library
 *
 * @package Framework_Controller
 */

namespace Framework\Controller;

use Framework\ServiceLocator\PluginManager\AbstractPluginManager;
use Framework\Loader\Autoloader;

class PluginManager extends AbstractPluginManager
{
    /**
     * Get the plugin class name
     *
     * @param string $name
     * @return string|boolean
     */
    public function getPluginClass($name)
    {
        $name = ucfirst($name);
        $class = Autoloader::find("Controller\\Plugin\\$name");
        return $class ?: false;
    }
}
