<?php
/**
 * Standard Library
 *
 * @package Framework_Controller
 */

namespace Framework\Controller\Plugin;

use Framework\Controller\AbstractActionController;

abstract class AbstractPlugin implements PluginInterface
{
    /**
     * @var AbstractActionController
     */
    protected $controller = null;

    /**
     * Set controller
     *
     * @param AbstractActionController $controller
     * @return AbstractPlugin
     */
    public function setController(AbstractActionController $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * Get controller
     *
     * @return AbstractActionController
     */
    public function getController()
    {
        return $this->controller;
    }
}
