<?php
/**
 * Standard Library
 *
 * @package
 */

namespace Framework\View\Renderer;

use Framework\ServiceLocator\ServiceLocator;
use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\View\Resolver\ResolverInterface;
use Framework\View\Resolver\Resolver;
use Framework\View\Model\ModelInterface;
use Framework\View\Variables;
use Framework\View\HelperManager;
use Framework\View\Helper\HelperInterface;

/**
 * @method \Framework\View\Helper\Action action() Action Name
 * @method \Framework\View\Helper\Controller controller() Controller Name
 * @method \Framework\View\Helper\PageId pageId() Controller + Action
 * @method \Framework\View\Helper\AliasPageId aliasPageId() Alias Page ID
 * @method \Framework\View\Helper\Cycle cycle(array $data = array(), $name = self::DEFAULT_NAME) Helper for alternating between set of values
 * @method \Framework\View\Helper\Date date($date, $format = 'Y-m-d H:i:s', $default = null) Format date
 * @method \Framework\View\Helper\Escape escape($str) Escape the html string
 * @method \Framework\View\Helper\SelfUrl selfUrl($query = null, $escape = true) Get current url
 * @method \Framework\View\Helper\Urlencode urlencode($str, $raw = true) urlencode
 * @method \Framework\View\Helper\Urldecode urldecode($str, $raw = true) urldecode
 * @method \Framework\Controller\Plugin\Param param(string $name, $default) Get the parameter value
 */
class PhpRenderer implements RendererInterface, ServiceLocatorAwareInterface
{
    /**
     * @var ResolverInterface
     */
    private $__resolver = null;

    /**
     * @var Variables
     */
    private $__vars = null;

    /**
     * @var Variables[]
     */
    private $__varsCached = array();

    /**
     * @var ServiceLocator|\Hints\ServiceLocator
     */
    private $locator = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->__vars = new Variables;
    }

    /**
     * Return the template engine object
     *
     * Returns the object instance, as it is its own template engine
     *
     * @return PhpRenderer
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Set template resolver
     *
     * @param ResolverInterface $resolver
     * @return PhpRenderer
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->__resolver = $resolver;
        return $this;
    }

    /**
     * Get template resolver
     *
     * @return ResolverInterface
     */
    public function getResolver()
    {
        return $this->__resolver ?: new Resolver();
    }

    /**
     * Set service locator
     *
     * @param ServiceLocator $serviceLocator
     * @return PhpRenderer
     */
    public function setServiceLocator(ServiceLocator $serviceLocator)
    {
        $this->locator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @throws \BadMethodCallException
     */
    public function getServiceLocator()
    {
        throw new \BadMethodCallException("It can't be invoked by outer class.");
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param string|ModelInterface $nameOrModel  The script/resource process, or a view model
     * @param array|\ArrayAccess $values  Values to use during rendering
     * @return string The script output.
     * @throws \RuntimeException
     */
    public function render($nameOrModel, $values = array())
    {
        if ($nameOrModel instanceof ModelInterface) {
            $model = $nameOrModel;
            $nameOrModel = $model->getTemplate();
            if (empty($nameOrModel)) {
                throw new \RuntimeException(sprintf(
                    '%s: received View Model argument, but template is empty',
                    __METHOD__
                ));
            }

            $values = $model->getVariables();
            unset($model);
        }

        // extract all assigned vars (pre-escaped), but not 'this'.
        if (array_key_exists('this', $values)) {
            unset($values['this']);
        }

        // cache the variables
        $this->__varsCached[] = $this->__vars;

        // save the variables to the renderer
        $this->setVariables($values);

        // render
        $template = $this->getResolver()->resolve($nameOrModel);
        ob_start();
        $this->_run((array) $values, $template);
        $___retval = ob_get_clean();

        // restore variables
        $this->setVariables(array_pop($this->__varsCached));

        // return
        return $___retval;
    }

    /**
     * Set variable storage
     *
     * Expects either an array, or an object implementing ArrayAccess.
     *
     * @param array|ArrayAccess $variables
     * @return PhpRenderer
     * @throws \InvalidArgumentException
     */
    public function setVariables($variables)
    {
        if (!is_array($variables) && !$variables instanceof \ArrayAccess) {
            throw new \InvalidArgumentException(sprintf(
                'Expected array or ArrayAccess object; received "%s"',
                (is_object($variables) ? get_class($variables) : gettype($variables))
            ));
        }

        // Enforce a Variables container
        if (!$variables instanceof Variables) {
            $variablesAsArray = array();
            foreach ($variables as $key => $value) {
                $variablesAsArray[$key] = $value;
            }
            $variables = new Variables($variablesAsArray);
        }

        $this->__vars = $variables;
        return $this;
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        // It will try to search from helpers if the $name is not set.
        if (!$this->__isset($name)) {
            /* @var $helpers HelperManager */
            $helpers = $this->locator->get('Framework\View\HelperManager');
            if ($helpers->exists($name)) {
                $helper = $this->helper($name);
                return $helper;
            }
        }

        // return
        return $this->__vars[$name];
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->__vars[$name] = $value;
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->__vars[$name]);
    }

    /**
     * Overloading: proxy to Variables container
     *
     * @param string $name
     * @return void
     */
    public function __unset($name)
    {
        if (!isset($this->__vars[$name])) {
            return;
        }
        unset($this->__vars[$name]);
    }

    /**
     * Get helper object
     *
     * @param string $name
     * @return object
     */
    public function helper($name)
    {
        /* @var $helpers HelperManager */
        $helpers = $this->locator->get('Framework\View\HelperManager');

        /* @var $helper Helper\HelperInterface */
        $helper = $helpers->get($name);

        // Set view for helper which is implement HelperInterface.
        if ($helper instanceof HelperInterface) {
            $helper->setView($this);
        }
        return $helper;
    }

    /**
     * Invoke the view helper
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @see HelperManager
     */
    public function __call($name, $arguments)
    {
        // Ensure set view
        $this->helper($name);

        /* @var $helpers HelperManager */
        $helpers = $this->locator->get('Framework\View\HelperManager');
        return $helpers->__call($name, $arguments);
    }

    /**
     * Include the template and extract the variables to it
     *
     * @param array|\ArrayAccess $__dumpValues
     * @param string $template
     * @return echo
     */
    protected function _run($__dumpValues)
    {
        extract($__dumpValues, EXTR_REFS);
        unset($__dumpValues);
        include func_get_arg(1);
    }
}
