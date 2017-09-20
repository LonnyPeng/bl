<?php
/**
 * Standard Library
 *
 * @package Framework_Controller
 */

namespace Framework\Controller\Plugin;

use Framework\Controller\AbstractActionController;

interface PluginInterface
{
    /**
     * Set controller
     *
     * @param AbstractActionController $controller
     * @return PluginInterface
     */
    public function setController(AbstractActionController $controller);

    /**
     * Get controller
     *
     * @return AbstractActionController
     */
    public function getController();
}
