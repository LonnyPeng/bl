<?php

namespace App\Controller\Plugin;

use Framework\ServiceLocator\ServiceLocatorAwareInterface;
use Framework\ServiceLocator\ServiceLocator;
use Framework\Utils\Http;

class Funcs implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocator
     */
    protected $locator = null;

    /**
     * Get the HTTP referer
     *
     * @param string $defaultUrl
     * @param boolean $checkHost
     * @return string
     */
    public function getReferer($defaultUrl = null, $checkHost = true)
    {
        // _POST['redirect'] or _GET['redirect']
        if (!empty($_REQUEST['redirect'])) {
            return $_REQUEST['redirect'];
        }

        // check _SERVER['HTTP_REFERER']
        if (!empty($_SERVER['HTTP_REFERER'])) {

            // check host
            if (!$checkHost || ($_SERVER['HTTP_HOST'] === parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST))) {
                return $_SERVER['HTTP_REFERER'];
            }
        }

        // use the default URL
        if (null === $defaultUrl) {
            /* @var $helpers \Framework\View\HelperManager */
            $helpers = $this->locator->get(HELPER_MANAGER);
            $defaultUrl = (string) $helpers->url();
        }
        return $defaultUrl;
    }

    /**
     * Redirect to some URL
     *
     * @param string $url
     * @param int $status
     * @return exit
     */
    public function redirect($url, $status = 302)
    {
        $url = (string) $url;

        // accept back or -1
        if ($url == 'back' || $url == '-1') {
            $url = $this->getReferer();
        }

        // headers were sent
        if (headers_sent()) {
            printf('<script>window.location="%s";</script>', $url);
            exit;
        }

        // redirect and exit
        Http::redirect($url, $status);
    }

    /**
     * Whether or not the POST request
     *
     * @return boolean
     */
    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Whether or not the AJAX request
     *
     * @return boolean
     */
    public function isAjax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
                || (isset($_REQUEST['X-Requested-With'])
                && $_REQUEST['X-Requested-With'] == 'XMLHttpRequest');
    }

    /**
     * generate 16 bits random
     *
     * @param int $bits
     * @return string
     */
    public function rand($bits = 16)
    {
        if ($bits == 16) {
            return substr(md5(uniqid(rand(), true)), 8, 16);
        } else {
            return md5(uniqid(rand(), true));
        }
    }

    /**
     * Create new file
     *
     * @param string $path
     * @return boolean
     */
    public function makeFile($path = "")
    {
        $path = trim($path);
        if (!$path) {
            return false;
        }

        $path = preg_replace("/\\\\/", "/", $path);

        $filename = substr($path, strripos($path, "/") + 1);
        $ext = substr($filename, strripos($filename, ".") + 1);
        if (!$ext) {
            $filename = "";
        }

        $dirPathInfo = explode("/{$filename}", $path);
        array_pop($dirPathInfo);
        $dirPath = implode("/", $dirPathInfo);

        if ($filename) {
            if (is_dir($path)) {
                return false;
            }

            if (file_exists($path)) {
                return true;
            }
        } else {
            if (is_dir($path)) {
                return true;
            }
        }

        // make dir
        if (!is_dir($dirPath)) {
            if (file_exists($dirPath)) {
                return false;
            }

            if (!@mkdir($dirPath, 0777, true)) {
                if (!is_dir($dirPath)) {
                    return false;
                }
            }
        }

        // make file
        if ($filename) {
            $handle = fopen($path, 'a');
            fclose($handle);
        }

        if (file_exists($path)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Intercept string
     * 
     * @param string $string
     * @param int $start
     * @param int $length
     * @return string
     */
    function mbSubString($string = "", $start = 0, $length = 0)
    {
        $length = (int) trim($length);
        $start = (int) trim($start);

        if ($length < 1) {
            $length = mb_strlen($string, "UTF-8");
        }

        $str = mb_substr($string, $start, $length, "UTF-8");
        if (mb_strlen($str, "UTF-8") < mb_strlen($string, "UTF-8")) {
            $str .= "...";
        }

        return $str;
    }

    public function setImage($path = null, $dir = '')
    {
        $insInit = array(
            'width' => 750,
            'height' => 380,
        );
        $data = array(
            'dir' => '', 'src' => '',
            'dir_x' => 0, 'dir_y' => 0,
            'src_x' => 0, 'src_y' => 0,
            'dir_w' => 0, 'dir_h' => 0,
            'src_w' => 0, 'src_h' => 0,
        );
        $key = array('imagecreatefromjpeg', 'imagejpeg');
        if (!file_exists($path)) {
            return array('status' => false, 'content' => '图片保存失败');
        }

        $pathinfo = getimagesize($path);
        if (!in_array($pathinfo['mime'], array('image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png'))) {
            return array('status' => false, 'content' => '请上传图片类型为 JPG, JPEG, PNG');
        } else {
            if (in_array($pathinfo['mime'], array('image/x-png', 'image/png'))) {
                $key = array('imagecreatefrompng', 'imagepng');
            } elseif (in_array($pathinfo['mime'], array('image/gif'))) {
                $key = array('imagecreatefromgif', 'imagegif');
            }
        }
        $pathinfo = array_merge($pathinfo, pathinfo($path));

        // new file path
        $fileName = explode('_origin', $pathinfo['filename']);
        $name = $fileName[0] . "." . $pathinfo['extension'];
        $dirFile = $pathinfo['dirname'] . "/" . $name;
        $newPath = str_replace($dir, '', $dirFile);

        // init image
        $srcImg = $key[0]($path);
        $dirImg = imagecreatetruecolor($insInit['width'], $insInit['height']);
        $color = imagecolorallocate($dirImg, 255, 255, 255);
        imagefill($dirImg, 0, 0, $color);

        if ($pathinfo[0] <= $insInit['width'] && $pathinfo[1] <= $insInit['height']) { //1 2 3 7
            $data = array(
                'dir' => $dirImg, 'src' => $srcImg,
                'dir_x' => ($insInit['width'] - $pathinfo[0]) / 2, 'dir_y' => ($insInit['height'] - $pathinfo[1]) / 2,
                'src_x' => 0, 'src_y' => 0,
                'dir_w' => $pathinfo[0], 'dir_h' => $pathinfo[1],
                'src_w' => $pathinfo[0], 'src_h' => $pathinfo[1],
            );
        } elseif ($pathinfo[0] == $insInit['width'] || $pathinfo[1] == $insInit['height']) { //4 5
            $data = array(
                'dir' => $dirImg, 'src' => $srcImg,
                'dir_x' => 0, 'dir_y' => 0,
                'src_x' => ($pathinfo[0] - $insInit['width']) / 2, 'src_y' => ($pathinfo[1] - $insInit['height']) / 2,
                'dir_w' => $insInit['width'], 'dir_h' => $insInit['height'],
                'src_w' => $insInit['width'], 'src_h' => $insInit['height'],
            );
        } elseif ($pathinfo[0] == $pathinfo[1]) { //6
            $data = array(
                'dir' => $dirImg, 'src' => $srcImg,
                'dir_x' => 0, 'dir_y' => 0,
                'src_x' => 0, 'src_y' => 0,
                'dir_w' => $insInit['width'], 'dir_h' => $insInit['height'],
                'src_w' => $pathinfo[0], 'src_h' => $pathinfo[1],
            );
        } else {
            $min = min($pathinfo[0], $pathinfo[1]);
            if ($min == $pathinfo[0]) {
                $width = $insInit['width'];
                $height = $pathinfo[1] * $insInit['height'] / $pathinfo[0];
            } else {
                $width = $pathinfo[0] * $insInit['width'] / $pathinfo[1];
                $height = $insInit['height'];
            }

            $abbreviationsImg = imagecreatetruecolor($width, $height);

            // copy abbreviations image
            imagecopyresampled($abbreviationsImg, $srcImg, 0, 0, 0, 0, $width, $height, $pathinfo[0], $pathinfo[1]);
            $key[1]($abbreviationsImg, $dirFile);
            imagedestroy($abbreviationsImg);

            return $this->setImage($dirFile, $dir);
        }

        // copy image
        imagecopyresampled($data['dir'], $data['src'], $data['dir_x'], $data['dir_y'], $data['src_x'], $data['src_y'], $data['dir_w'], $data['dir_h'], $data['src_w'], $data['src_h']);

        // save image
        $key[1]($dirImg, $dirFile);

        // close image
        imagedestroy($srcImg);
        imagedestroy($dirImg);

        return array('status' => true, 'content' => $newPath);
    }

    public function showTime($time) {
        $time = strtotime(trim($time));
        $time -= time();
        if ($time < 0) {
            $time = 0;
        }

        $init = [
            'day' => [86400, '天'],
        ];

        $str = '';
        foreach ($init as $key => $row) {
            $num = floor($time / $row[0]) + 1;
            if (!$num) {
                continue;
            }
            
            $str .= $num . $row[1];
            $time -= $num * $row[0];
        }
        
        return $str;
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