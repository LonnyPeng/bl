<?php

namespace App\View\Helper;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;
use Framework\Utils;

/**
 * @example
 *  <script src="<?php echo $this->js('jquery.js')?>"></script>
 *  $this->js('jquery.js', true)->wrap()
 *  $this->js('/js/jquery.js')->wrap()
 *  $this->js('//example.com/jquery.js')->wrap()
 *  $this->js('http://example.com/jquery.js')->wrap()
 */
class Js implements ServiceLocatorAwareInterface
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
     * get the url of javascript file
     *
     * @param string $file
     * @param boolean $checkExists (optional)
     * @return Js
     */
	public function __invoke($file, $checkExists = false)
	{
        if (strpos($file, '//') !== false || ($file && $file{0} == '/')) {
            $this->url = $file;
            return $this;
        }

        if ($checkExists && !file_exists(JS_DIR . $file)) {
            $this->url = null;
        }
        else {
            $this->url = BASE_PATH . 'js/' . $file;
        }
		return $this;
	}

    /**
     * create script tag
     *
     * @param string $attrs (optional)
     * @return string
     */
	public function wrap($attrs = null)
	{
        if ($this->url) {
            return sprintf("<script src=\"%s\"%s></script>\n", $this->url, Utils\FrameworkString::concat($attrs));
        }
        return '';
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