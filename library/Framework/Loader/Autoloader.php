<?php
/**
 * Standard Library
 *
 * @package Framework_Loader
 */

namespace Framework\Loader;

/**
 * Standard autoload class
 */
class Autoloader
{
    /**
     * @var array
     */
    protected static $namespaces = array();

    /**
     * @param array $namespaces
     * @example
     *  Autoloader::setNamespaces(
     *      'Framework'  => LIB_DIR,
     *      'Zend'  => LIB_DIR,
     *      'App'   => APP_DIR . 'class/',
     *  );
     */
    public static function setNamespaces(array $namespaces)
    {
        self::$namespaces = $namespaces;
    }

    /**
     * set a namespace
     *
     * @param string $name
     * @param string $path
     */
    public static function setNamespace($name, $path)
    {
        self::$namespaces[$name] = $path;
    }

    /**
     * Load a class
     *
     * @param string $className
     * @param string $suffix (optional)
     * @return boolean
     * @example
     *  Autoloader::load('Smarty', '.class.php');
     *  Autoloader::load('Zend\Loader', '.class.php');
     */
    public static function load($className, $suffix = '.php')
    {
        $ns = self::$namespaces;

        // find Class by the namespace
        $trunk = strtr($className, array('_' => '/', '\\' => '/'));
        $prefix = strstr($trunk, '/', true);
        if (isset($ns[$prefix])) {
            $file = $ns[$prefix] . $trunk . $suffix;
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }

        // find Class from every namespace
        $ns = array_reverse($ns);
        foreach ($ns as $path) {
            $file = $path . $trunk . $suffix;
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }

        // failure
        return false;
    }

    /**
     * Find a class from the namespaces
     *
     * @param string $baseName
     * @param string $suffix
     * @return string|boolean
     * @example
     *  $helper = 'Escape';
     *  Autoloader::find("View\Helper\$helper");
     *  => Framework\View\Helper\Escape
     */
    public static function find($baseName, $suffix = '.php')
    {
        $ns = array_reverse(self::$namespaces);
        foreach ($ns as $namespace => $path) {
            $trunk = $namespace . '/' . strtr($baseName, array('_' => '/', '\\' => '/'));
            if (file_exists($path . $trunk . $suffix)) {
                return "$namespace\\$baseName";
            }
        }
        return false;
    }

    /**
     * register autoload
     */
    public static function register()
    {
        spl_autoload_register(array('static', 'load'));
    }
}