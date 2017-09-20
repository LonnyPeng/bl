<?php

namespace App\View\Helper;

class UploadUrl
{
    /**
     * @var string
     */
	protected $url = null;

    /**
     * Get the url of photo file
     *
     * @param string $file
     * @param string $type
     * @return string
     */
    public function __invoke($file, $type)
    {
        if (strpos($file, '//') !== false || ($file && $file{0} == '/')) {
            $this->url = $file;
            return $this;
        }

        $this->url = '/index/upload/' . $type . '/' . $file;
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
}
