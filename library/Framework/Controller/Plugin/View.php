<?php
/**
 * Standard Library
 *
 * @package Framework_Controller
 * @subpackage Framework_Controller_Plugin
 */

namespace Framework\Controller\Plugin;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;
use Framework\ServiceLocator\PluginManager\FactoryInterface;

class View extends AbstractPlugin implements ServiceLocatorAwareInterface, FactoryInterface
{
    /**
     * @var ServiceLocator
     */
    protected $locator = null;

    /**
     * Get View Object
     *
     * @return \Framework\View\View
     * @todo remove TPL_DIR constant
     */
    public function factory()
    {
        /* @var $resolver \Framework\View\Resolver\Resolver */
        $resolver = $this->locator->get('Framework\View\Resolver\Resolver');
        $resolver->addPath(TPL_DIR);

        /* @var $renderer \Framework\View\Renderer\PhpRenderer */
        $renderer = $this->locator->get('Framework\View\Renderer\PhpRenderer');
        $renderer->setResolver($resolver);

        /* @var $view \Framework\View\View */
        $view = $this->locator->get('Framework\View\View');
        $view->setRenderer($renderer);

        // return
        return $view;
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