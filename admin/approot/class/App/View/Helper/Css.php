<?php

namespace App\View\Helper;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;
use Framework\Utils;

/**
 * @example
 *  <script src="<?php echo $this->css('public.css')?>"></script>
 *  $this->css('public.css', true)->wrap()
 *  $this->css('/css/public.css')->wrap()
 *  $this->css('//example.com/public.css')->wrap()
 *  $this->css('http://example.com/public.css')->wrap()
 */
class Css implements ServiceLocatorAwareInterface
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
     * @param boolean $checkExists (optional)
     * @return Css
     */
	public function __invoke($file, $checkExists = false)
	{
        if (strpos($file, '//') !== false || ($file && $file{0} == '/')) {
            $this->url = $file;
            return $this;
        }

        if ($checkExists && !file_exists(CSS_DIR . $file)) {
            $this->url = null;
        }
        else {
            $this->url = BASE_PATH . 'css/' . $file;
        }
		return $this;
	}

    /**
     * create css tag
     *
     * @param string $attrs
     * @return string
     */
	public function wrap($attrs = null)
	{
        if ($this->url) {
            $rel = substr($this->url, -9) == '.less.css' ? 'stylesheet/less' : 'stylesheet';
            return sprintf("<link rel=\"%s\" href=\"%s\"%s>\n", $rel, $this->url, Utils\FrameworkString::concat($attrs));
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