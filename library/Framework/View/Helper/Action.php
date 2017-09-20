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

class Action implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocator
     */
    protected $locator = null;

    /**
     * format date
     *
     * @param string|int $date
     * @param string $format
     * @param string $default
     * @return string
     */
    public function __invoke()
    {
        return $this->locator->get('ActionName');
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
