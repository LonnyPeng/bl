<?php
/**
 * Standard Library
 *
 * @package Framework_Model
 */

namespace Framework\Model;

use Framework\ServiceLocator\PluginManager\AbstractPluginManager;
use Framework\Loader\Autoloader;

class ModelManager extends AbstractPluginManager
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
        $class = Autoloader::find("Model\\{$name}Model");
        return $class ?: false;
    }
}
