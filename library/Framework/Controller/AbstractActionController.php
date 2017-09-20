<?php
/**
 * Standard Library
 *
 * @package Framework_Controller
 */

namespace Framework\Controller;

use Framework\Event\AbstractEventDispatcher;
use Framework\ServiceLocator\ServiceLocator;
use Framework\Controller\Plugin\PluginInterface;
use Framework\View\Model\ModelInterface;

/**
 * @property \Framework\View\HelperManager|\Hints\HelperManager $helpers The helper manager
 * @property \Framework\Model\ModelManager|\Hints\ModelManager $models The model manager
 *
 * @property \Framework\Controller\Plugin\Layout $layout
 * @property \Framework\Controller\Plugin\Model $model
 * @property \Framework\View\View $view
 *
 * @method \Framework\Controller\Plugin\Layout layout($model) The layout plugin
 * @method \Framework\Controller\Plugin\Model model($method) Get the view model from other method of controller
 * @method \Framework\Controller\Plugin\Param param(string $name, $default) Get the parameter value
 * @method \Framework\View\View view()
 */
abstract class AbstractActionController extends AbstractEventDispatcher
{
    /**
     * @var string Default Action
     */
    const DEFAULT_ACTION = 'index';

    /**
     * save the layout model, also {@see Plugin\Layout}
     *
     * @var ModelInterface
     */
    protected $layout = null;

    /**
     * Constructor
     */
    public function __construct(ServiceLocator $locator)
    {
        // set service locator
        $this->setServiceLocator($locator);

        // register view helpers
        $this->helpers->register('param', function() {
            return $this->plugin('param');
        });

        // initialize
        $this->init();
    }

    /**
     * Triggered by {@link __construct() the constructor} as its final action.
     *
     * @return void
     */
    public function init()
    {}

    /**
     * Set layout
     *
     * @param ModelInterface $model
     * @return AbstractActionController
     * @see Plugin\Layout
     */
    public function setLayout(ModelInterface $model = null)
    {
        $this->layout = $model;
        return $this;
    }

    /**
     * Get layout
     *
     * @return ModelInterface
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Get plugin object
     *
     * @ignore
     * @param string $name
     * @return object
     */
    public function plugin($name)
    {
        /* @var $plugins PluginManager */
        $plugins = $this->locator->get('Framework\Controller\PluginManager');

        /* @var $plugin Plugin\PluginInterface */
        $plugin = $plugins->get($name);

        if ($plugin instanceof PluginInterface) {
            $plugin->setController($this);
        }

        return $plugin;
    }

    /**
     * Invoke the controller plugin
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @see PluginManager
     */
    public function __call($name, $arguments)
    {
        // Ensure set controller
        $this->plugin($name);

        /* @var $plugins PluginManager */
        $plugins = $this->locator->get('Framework\Controller\PluginManager');
        return $plugins->__call($name, $arguments);
    }

    /**
     * You can invoke the model manager, helper manager or controller plugin
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        // model manager
        if ('models' === $name) {
            return $this->locator->get('Framework\Model\ModelManager');
        }

        // view helper manager
        if ('helpers' === $name) {
            return $this->locator->get('Framework\View\HelperManager');
        }

        // controller plugin instance
        return $this->plugin($name);
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
}