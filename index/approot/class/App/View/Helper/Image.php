<?php

namespace App\View\Helper;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;

class Image implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocator
     */
    protected $locator = null;

    /**
     * @var string
     */
	protected $url = null;

    /**
     * get the url of css file
     *
     * @param string $file
     * @return Image
     */
	public function __invoke($file, $forceHost = false)
	{
        if (strpos($file, '//') !== false || ($file && $file{0} == '/')) {
            $this->url = $file;
            return $this;
        }

        $this->url = BASE_PATH . 'image/' . $file;
        if ($forceHost) {
            $this->url = "http://" . $_SERVER['HTTP_HOST'] . $this->url;
        }
		return $this;
	}

    /**
     * Magic method
     *
     * @return string
     */
	public function __toString()
	{
		return $this->url;
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