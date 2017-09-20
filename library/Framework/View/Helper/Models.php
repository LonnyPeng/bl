<?php
/**
 * Standard Library
 *
 * @package Framework_View
 * @subpackage Framework_View_Helper
 */

namespace Framework\View\Helper;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;

class Models implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocator
     */
    protected $locator = null;

    public function __get($name)
    {
        $models = $this->locator->get('Framework\Model\ModelManager');
        return $models->{$name};
    }

    /**
     * Set service locator
     *
     * @param ServiceLocator $locator
     * @return Models
     */
    public function setServiceLocator(ServiceLocator $locator)
    {
        $this->locator = $locator;
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