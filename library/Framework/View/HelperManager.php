<?php
/**
 * Standard Library
 *
 * @package Framework_View
 */

namespace Framework\View;

use Framework\ServiceLocator\PluginManager\AbstractPluginManager;
use Framework\Loader\Autoloader;

/**
 * @property \Framework\View\Helper\Action $action
 * @property \Framework\View\Helper\Controller $controller
 * @property \Framework\View\Helper\PageId $pageId
 * @property \Framework\View\Helper\Cycle $cycle
 * @property \Framework\View\Helper\Date $date
 * @property \Framework\View\Helper\Escape $escape
 * @property \Framework\View\Helper\Url $url
 * @property \Framework\View\Helper\SelfUrl $selfUrl
 * @property \Framework\View\Helper\Urlencode $urlencode
 * @property \Framework\View\Helper\Urldecode $urldecode
 *
 * @method \Framework\View\Helper\Action action() Action Name
 * @method \Framework\View\Helper\Controller controller() Controller Name
 * @method \Framework\View\Helper\PageId pageId() Controller + Action
 * @method \Framework\View\Helper\Cycle cycle(array $data = array(), $name = self::DEFAULT_NAME) Helper for alternating between set of values
 * @method \Framework\View\Helper\Date date($date, $format = 'Y-m-d H:i:s', $default = null) Format date
 * @method \Framework\View\Helper\Escape escape($str) Escape the html string
 * @method \Framework\View\Helper\Url url($path = 'default/index', $params = null, $https = false, $forceHost = false)
 * @method \Framework\View\Helper\SelfUrl selfUrl($query = null, $escape = true) Get current url
 * @method \Framework\View\Helper\Urlencode urlencode($str, $raw = true) urlencode
 * @method \Framework\View\Helper\Urldecode urldecode($str, $raw = true) urldecode
 * @method \Framework\Controller\Plugin\Param param(string $name, $default) Get the parameter value
 */
class HelperManager extends AbstractPluginManager
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
        $class = Autoloader::find("View\\Helper\\$name");
        return $class ?: false;
    }
}
