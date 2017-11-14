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
    public function __invoke($file, $type, $forceHost = false)
    {
        if (strpos($file, '//') !== false || ($file && $file{0} == '/')) {
            $this->url = $file;
            return $this;
        }

        if ($type == 'user' && !file_exists(USER_DIR . $file)) {
            $this->url = BASE_PATH . 'image/head_img.jpg';
        } else {
            $this->url = '/index/upload/' . $type . '/' . $file;
        }
        
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
}
