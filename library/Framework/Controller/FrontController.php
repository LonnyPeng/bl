<?php
/**
 * Standard Library
 *
 * @package Framework_Controller
 */

namespace Framework\Controller;

use Framework\Loader\Autoloader;
use Framework\Event\AbstractEventDispatcher;
use Framework\Utils\WordConvertor;
use Framework\Controller\AbstractActionController;
use Framework\View\View;
use Framework\View\Model\ModelInterface;
use Framework\View\Model\ViewModel;
use Framework\View\Model\JsonModel;

class FrontController extends AbstractEventDispatcher
{
    /**
     * The default controller name
     *
     * @var string
     */
    protected $defaultControllerName = 'default';

    /**
     * The default notfound action name
     *
     * @var string
     */
    protected $notFoundActionName = 'not-found';

    /**
     * current controller name
     *
     * @var string
     */
    protected $controllerName = null;

    /**
     * current action name
     *
     * @var string
     */
    protected $actionName = null;

    /**
     * current controller object
     *
     * @var AbstractActionController
     */
    protected $controller = null;

    /**
     * @var View
     */
    protected $view = null;

    /**
     * Set the default controller name
     *
     * @param string $name
     * @return FrontController
     */
    public function setDefaultControllerName($name)
    {
        $this->defaultControllerName = $name;
        return $this;
    }

    /**
     * Get the default controller name
     *
     * @return string
     */
    public function getDefaultControllerName()
    {
        return $this->defaultControllerName;
    }

    /**
     * Set the default action name
     *
     * @param string $name
     * @return FrontController
     */
    public function setDefaultActionName($name)
    {
        $this->defaultActionName = $name;
        return $this;
    }

    /**
     * Set the notfound action name
     *
     * @param string $name
     * @return FrontController
     */
    public function setNotFoundActionName($name)
    {
        $this->notFoundActionName = $name;
        return $this;
    }

    /**
     * Get the notfound action name
     *
     * @return string
     */
    public function getNotFoundActionName()
    {
        return $this->notFoundActionName;
    }

    /**
     * Get the controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * Get the action name
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Get the template virtual name
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->getControllerName() . '/' . $this->getActionName();
    }

    /**
     * Dispatch
     *
     * @param string $controllerName
     * @param string $actionName
     * @return mixed|false
     */
    public function dispatch($controllerName = null, $actionName = null)
    {
        // if (empty($controllerName)) {
        //     $controllerName = $this->getDefaultControllerName();
        // }

        $controllerClassName = $this->formatControllerName($controllerName);
        $actionMethodName = $this->formatActionName($actionName);
        $result = $this->find($controllerClassName, $actionMethodName);
        return $result;
    }

    /**
     * Process the result
     * You can override it
     *
     * @param ModelInterface|array|\Traversable|false|null $result
     * @return echo
     */
    public function run($result)
    {
        // don't use view
        if (false === $result || PHP_SAPI == 'cli') {
            return;
        }

        // json model
        if ($result instanceof JsonModel) {
            /* @var $model JsonModel */
            echo $result->serialize();
            return;
        }

        // define view model
        if ($result instanceof ModelInterface) {
            $model = $result;
        } else {
            $model = new ViewModel($result);
        }
        if (!$model->getTemplate()) {
            $model->setTemplate($this->getTemplate());
        }

        /* @var $layout ModelInterface */
        $layout = $this->controller->getLayout();

        /* @var $view View */
        $view = $this->controller->view();

        // use layout
        if ($layout) {
            $layout->addChild($model);
            echo $view->render($layout);
        }
        // don't use layout
        else {
            echo $view->render($model);
        }
    }

    /**
     * Find the right action of the right controller
     * It will set these services: Controller, ControllerName, ActionName
     *
     * @param string $controllerClassName
     * @param string $actionMethodName
     * @return mixed|false
     * @throws \RuntimeException
     */
    protected function find($controllerClassName, $actionMethodName)
    {
        $fullControllerClassName = Autoloader::find("Controller\\$controllerClassName");

        // Don't exist the controller
        if (!$fullControllerClassName) {
            $defaultControllerClassName = $this->formatControllerName($this->getDefaultControllerName());

            // already the default controller name
            if ($defaultControllerClassName == $controllerClassName) {
                throw new \RuntimeException("Invalid default controller class name: $controllerClassName");
                return false;
            }

            // find next
            $notFoundActionMethodName = $this->formatActionName($this->getNotFoundActionName());
            return $this->find($defaultControllerClassName, $notFoundActionMethodName);
        }

        // get the default action of controller if $actionMethodName is empty
        // if (empty($actionMethodName)) {
        //     $actionMethodName = $this->formatActionName($fullControllerClassName::DEFAULT_ACTION);
        // }

        // Exist the controller, but don't exist the action
        if (!method_exists($fullControllerClassName, $actionMethodName)) {

            $notFoundActionMethodName = $this->formatActionName($this->getNotFoundActionName());

            // already be the notfound action
            if ($notFoundActionMethodName == $actionMethodName) {

                // use the notfound action of default controller
                $defaultControllerClassName = $this->formatControllerName($this->getDefaultControllerName());

                // already the default controller name
                if ($defaultControllerClassName == $controllerClassName) {

                    // use the default action of default controller
                    $fullDefaultControllerClassName = Autoloader::find("Controller\\$defaultControllerClassName");
                    if (!$fullDefaultControllerClassName) {
                        throw new \RuntimeException("Invalid default controller: $defaultControllerClassName");
                        return false;
                    }

                    // default action method name
                    $defaultActionMethodName = $this->formatActionName($fullDefaultControllerClassName::DEFAULT_ACTION);
                    if (!method_exists($fullControllerClassName, $defaultActionMethodName)) {
                        throw new \RuntimeException("Invalid default action: {$fullDefaultControllerClassName}::{$defaultActionMethodName}");
                        return false;
                    }

                    // aready the default action name
                    if ($defaultActionMethodName == $actionMethodName) {
                        throw new \RuntimeException("Invalid default action method name: $actionMethodName");
                        return false;
                    }

                    // find next
                    return $this->find($defaultControllerClassName, $defaultActionMethodName);
                }

                // find next
                return $this->find($defaultControllerClassName, $notFoundActionMethodName);
            }

            // find next
            return $this->find($controllerClassName, $notFoundActionMethodName);
        }

        // Exist the controller, and exist the action
        $this->controllerName = $this->reflectControllerName($controllerClassName);
        $this->actionName = $this->reflectActionName($actionMethodName);

        // set service
        if ($this->locator) {
            $this->locator->setService('ControllerName', $this->controllerName);
            $this->locator->setService('ActionName', $this->actionName);
        }

        /* @var $controller \Framework\Controller\AbstractActionController */
        $controller = new $fullControllerClassName($this->getServiceLocator());
        if ($this->locator) {
            $this->locator->setService('Controller', $controller);
        }

        // set the controller object & controller name & action name
        $this->controller = $controller;

        // get result
        $result = $controller->$actionMethodName();

        // return
        return $result;
    }

    /**
     * Format a controller name
     * e.g. default-name => DefaultNameController
     *
     * @param string $controllerName
     * @return string
     */
    public function formatControllerName($controllerName)
    {
        if (empty($controllerName)) {
            return null;
        }
        $controllerName = strtolower($controllerName);
        return WordConvertor::dashToCamelCase($controllerName) . 'Controller';
    }

    /**
     * Format a action name
     * e.g. action-name => actionNameAction
     *
     * @param string $actionName
     * @return string
     */
    public function formatActionName($actionName)
    {
        if (empty($actionName)) {
            return null;
        }
        $actionName = strtolower($actionName);
        return WordConvertor::dashToCamelCase($actionName, true) . 'Action';
    }

    /**
     * Reflect controller name
     *
     * $formtedName can be the full controller name
     * e.g. App\Controller\DefaultNameController => default-name
     *
     * @param string $formatedName
     * @return string
     */
    public function reflectControllerName($formatedName)
    {
        $formatedName = preg_replace('/^.*(\w+)Controller$/U', '${1}', $formatedName);
        $name = WordConvertor::camelCaseToDash($formatedName, true);
        return $name;
    }

    /**
     * Reflect action name
     * e.g. actionNameAction => action-name
     *
     * @param stringe $formatedName
     * @return string
     */
    public function reflectActionName($formatedName)
    {
        $formatedName = preg_replace('/Action$/', '', $formatedName);
        $name = WordConvertor::camelCaseToDash($formatedName, true);
        return $name;
    }
}
