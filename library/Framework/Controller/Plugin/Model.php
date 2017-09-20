<?php
/**
 * Standard Library
 *
 * @package Framework_Controller
 * @subpackage Framework_Controller_Plugin
 */

namespace Framework\Controller\Plugin;

use Framework\View\Model\ModelInterface;
use Framework\View\Model\ViewModel;
use Framework\Loader\Autoloader;
use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;

/**
 * Get the view model from other method of controller
 */
class Model extends AbstractPlugin implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocator
     */
    protected $locator = null;

    /**
     * Saved models
     *
     * @var array
     */
    protected $models = array();

    /**
     * Get a model from the method of controller
     * It also can pass some parameters.
     *
     * @param string|array $method
     * @param mixed $more_parameters
     * @return ModelInterface
     */
    public function __invoke($method)
    {
        $controller = $this->getController();
        $obj = null;

        /** @example $this->__invoke('listAction') */
        if (is_string($method)) {
            $current = true;
            $obj = $controller;
            $fullControllerClassName = get_class($controller);
            $actionMethodName = $method;
        }
        elseif (is_array($method)) {
            $current = false;

            /** @example $this->__invoke(array('ProductController', 'listAction')) */
            if (is_string($method[0])) {
                $fullControllerClassName = Autoloader::find("Controller\\{$method[0]}");
                if (!$fullControllerClassName) {
                    throw new \InvalidArgumentException("Invalid controller class name: " . $controllerClassName);
                    return false;
                }
            }
            /** @example $this->__invoke(array($this, 'listAction')) */
            else {
                $obj = $method[0];
                $fullControllerClassName = get_class($obj);
            }
            $actionMethodName = $method[1];
        }
        else {
            throw new \InvalidArgumentException("Invliad first parmaeter");
            return false;
        }

        // called parameters
        $args = func_get_args();
        array_shift($args);

        // get the hash name of result
        $hash = $fullControllerClassName . '::' . $actionMethodName . ':' . ($args ? md5(serialize($args)) : '');

        // already exists
        if (array_key_exists($hash, $this->models)) {
            return $this->models[$hash];
        }

        // get $obj
        if (!$obj) {
            $obj = new $fullControllerClassName($this->locator);
        }

        // execute
        $result = call_user_func_array(array($obj, $actionMethodName), $args);

        // don't use view
        if (false === $result) {
            $this->models[$hash] = false;
            return false;
        }

        // define view model
        if ($result instanceof ModelInterface) {
            $model = $result;
        } else {
            $model = new ViewModel($result);
        }
        if (!$model->getTemplate()) {
            /* @var $front \Framework\Controller\FrontController */
            $front = $this->locator->get('FrontController');
            $template = $front->reflectControllerName($fullControllerClassName) . '/' . $front->reflectActionName($actionMethodName);
            $model->setTemplate($template);
        }

        // save & return
        return $this->models[$hash] = $model;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocator $serviceLocator
     * @return Controller
     */
    public function setServiceLocator(ServiceLocator $serviceLocator)
    {
        $this->locator = $serviceLocator;
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
}