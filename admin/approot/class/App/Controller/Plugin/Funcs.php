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

    /**
     * Get the file information
     *
     * @param string $filename
     * @return array
     */
    public function getFileInfo($filename = "")
    {
        $pregArr = array(
            "/\f/i" => "/f", "/\n/i" => "/n", "/\r/i" => "/r",
            "/\t/i" => "/t", "/\v/i" => "/v", "/\\\\/" => "/",
        );
        $imgType = array("jpg", "gif", "png");

        // init file path
        foreach ($pregArr as $key => $value) {
            $filename = preg_replace($key, $value, $filename);
        }

        if (!file_exists($filename)) {
            return false;
        }

        $pathinfo = pathinfo($filename);
        if (isset($pathinfo['extension']) && in_array(strtolower($pathinfo['extension']), $imgType)) {
            $imgInfo = getimagesize($filename);
            $pathinfo = array_merge($imgInfo, $pathinfo);
        }

        return $pathinfo;
    }

    public function setImage($path = null, $dir = '', $width = 750, $height = 380)
    {
        $insInit = array(
            'width' => $width,
            'height' => $height,
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

            return $this->setImage($dirFile, $dir, $insInit['width'], $insInit['height']);
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
     * PHP >>> ( 100 >>> 2 => 25)
     *
     * @param int $a
     * @param int $b
     * @return int
     */
    public function zeroFill($a = "", $b = "") 
    { 
        $z = hexdec(80000000); 
        if ($z & $a) { 
            $a = $a >> 1; 
            $a &= ~$z; 
            $a |= 0x40000000; 
            $a = $a >> ($b - 1); 
        } else { 
            $a = $a >> $b; 
        } 
        return $a; 
    }

    /**
     * 汉字 UNICODE编码
     *
     * @param string $name
     * @return int
     */
    public function unicodeEncode($name = "")  
    {
        if (ord($name) > 127) {
            $name = iconv('UTF-8', 'UCS-2', $name);
            $str = '';  
            for($i = 0; $i < strlen($name) - 1; $i += 2) {
                $c = $name[$i];
                $c2 = $name[$i + 1];
                if (ord($c) > 0) {
                    $str .= str_pad(base_convert(ord($c), 10, 16), 2, 0, STR_PAD_LEFT) . str_pad(base_convert(ord($c2), 10, 16), 2, 0, STR_PAD_LEFT);
                } else {  
                    $str .= $c2;  
                }  
            }

            $str = hexdec($str);
        } else {
            $str = ord($name);
        }

        return $str;  
    }

    /**
     * Use the curl virtual browser
     *
     * @param array $urlInfo = array('url' => "https://www.baidu.com/", 'params' => array('key' => 'test'), 'cookie' => 'cookie')
     * @param string $type = 'GET|POST'
     * @param boolean $info = false|true
     * @return string|array
     */
    public function curl($urlInfo, $type = "GET", $info = false) {
        $type = strtoupper(trim($type));

        if (isset($urlInfo['cookie'])) {
            $cookie = $urlInfo['cookie'];
            unset($urlInfo['cookie']);
        }

        if ($type == "POST") {
            $url = $urlInfo['url'];
            $data = $urlInfo['params'];
        } else {
            $urlArr = parse_url($urlInfo['url']);

            if (isset($urlInfo['params'])) {
                $params = "";
                foreach ($urlInfo['params'] as $key => $row) {
                    if (is_array($row)) {
                        foreach ($row as $value) {
                            if ($params) {
                                $params .= "&" . $key . "=" . $value;
                            } else {
                                $params .= $key . "=" . $value;
                            }
                        }
                    } else {
                        if ($params) {
                            $params .= "&" . $key . "=" . $row;
                        } else {
                            $params .= $key . "=" . $row;
                        }
                    }
                }
                
                if (isset($urlArr['query'])) {
                    if (preg_match("/&$/", $urlArr['query'])) {
                        $urlArr['query'] .= $params;
                    } else {
                        $urlArr['query'] .= "&" . $params;
                    }
                } else {
                    $urlArr['query'] = $params;
                }
            }

            if (isset($urlArr['host'])) {
                if (isset($urlArr['scheme'])) {
                    $url = $urlArr['scheme'] . "://" . $urlArr['host'];
                } else {
                    $url = $urlArr['host'];
                }

                if (isset($urlArr['port'])) {
                    $url .= ":" . $urlArr['port'];
                }
                if (isset($urlArr['path'])) {
                    $url .= $urlArr['path'];
                }
                if (isset($urlArr['query'])) {
                    $url .= "?" . $urlArr['query'];
                }
                if (isset($urlArr['fragment'])) {
                    $url .= "#" . $urlArr['fragment'];
                }
            } else {
                $url = $urlInfo['url'];
            }
        }
        
        $httpHead = array(
            "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Cache-Control:no-cache",
            "Connection:keep-alive",
            "Pragma:no-cache",
            "Upgrade-Insecure-Requests:1",
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (isset($cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE , $cookie);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHead);
        curl_setopt($ch, CURLOPT_ENCODING , "gzip");
        if ($type == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);
        $result = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch); 
        
        if ($info) {
            return $curlInfo;
        } else {
            return $result;
        }
    }

    /**
     * Get TKK
     *
     * @return string
     */
    public function TKK() {
        $preg = array(
            'tkk' => "#TKK\=eval\('\(\(function\(\)\{var\s+a\\\\x3d(-?\d+);var\s+b\\\\x3d(-?\d+);return\s+(\d+)\+#isU",
        );
        $html = $this->curl(array('url' => "http://translate.google.cn"));
        preg_match($preg['tkk'], $html, $arr);

        return $arr[3] . '.' . ($arr[1] + $arr[2]);
    }

    /**
     * 服务于 Google 翻译 tk 值
     *
     * @param int $a
     * @param string $b
     * @return int
     */
    public function b($a = "", $b = "") {
        for ($d = 0; $d < mb_strlen($b, "UTF-8") - 2; $d += 3) {
            $c = mb_substr($b, $d + 2, 1);
            if ("a" <= $c) {
                $c = ord(mb_substr($c, 0, 1, "UTF-8")) - 87;
            } else {
                $c = (int) $c;
            }

            if ("+" == mb_substr($b, $d + 1, 1, "UTF-8")) {
                $c = $this->zeroFill($a, $c);
            } else {
                $c = $a << $c;
            }

            if ("+" == mb_substr($b, $d, 1, "UTF-8")) {
                $a = $a + $c & 4294967295;
            } else {
                $a = $a ^ $c;
            }
        }
            
        return $a;
    }

    /**
     * 获取 Google 翻译 tk 值
     *
     * @param string $a (要翻译的内容)
     * @param string $b
     * @return string
     */
    public function tk($a = "", $TKK = "") {
        $e = explode(".", $TKK);
        if (isset($e[0])) {
            $h = floatval($e[0]);
        } else {
            $h = 0;
        }
        $g = array();
        $d = 0;
        for ($f = 0; $f < mb_strlen($a, "UTF-8"); $f++) {
            $c = $this->unicodeEncode(mb_substr($a, $f, 1, "UTF-8"));
            if (128 > $c) {
                $g[$d++] = $c;
            } else {
                if (2048 > $c) {
                    $g[$d++] = $c >> 6 | 192;
                } else {
                    if (55296 == ($c & 64512) 
                        && $f + 1 < mb_strlen($a, "UTF-8") 
                        && 56320 == ($this->unicodeEncode(mb_substr($a, $f + 1, 1, "UTF-8")) & 64512)) {
                        $c = 65536 + (($c & 1023) << 10) + ($this->unicodeEncode(mb_substr($a, ++$f, 1, "UTF-8")) & 1023);
                        $g[$d++] = $c >> 18 | 240;
                        $g[$d++] = $c >> 12 & 63 | 128;
                    } else {
                        $g[$d++] = $c >> 12 | 224;
                    }

                    $g[$d++] = $c >> 6 & 63 | 128;
                }

                $g[$d++] = $c & 63 | 128;
            }
        }

        $a = $h;
        for ($d = 0; $d < count($g); $d++) {
            $a += $g[$d];
            $a = $this->b($a, "+-a^+6");
        }

        $a = $this->b($a, "+-3^+b+-f");
        if (isset($e[1])) {
            $a = floatval($a) ^ floatval($e[1]);
        } else {
            $a ^= 0;
        }
        if (0 > $a) {
            $a = ($a & 2147483647) + 2147483648;
        }
        $a = fmod(floatval($a), 1E6);


        return (string) $a . "." . ($a ^ $h);
    }

    /**
     * Google translation api
     *
     * @param array $tranInfo = array('tl' => 'zh-CN', 'text' => "Hello World")
     * @return string
     */
    function translateGoogleApi($tranInfo = array('tl' => 'en', 'text' => ['Hello World']), $status = false)
    {
        $langArr = array(
            "sq", "ar", "am", "az", "ga", "et", "eu", "be", "bg", "is", "pl", "bs", "fa", "af", "da", "de", "ru", "fr", "tl", "fi", 
            "fy", "km", "ka", "gu", "kk", "ht", "ko", "ha", "nl", "ky", "gl", "ca", "cs", "kn", "co", "hr", "ku", "la", "lv", "lo", 
            "lt", "lb", "ro", "mg", "mt", "mr", "ml", "ms", "mk", "mi", "mn", "bn", "my", "hmn", "xh", "zu", "ne", "no", "pa", "pt", 
            "ps", "ny", "ja", "sv", "sm", "sr", "st", "si", "eo", "sk", "sl", "sw", "gd", "ceb", "so", "tg", "te", "ta", "th", "tr", 
            "cy", "ur", "uk", "uz", "es", "iw", "el", "haw", "sd", "hu", "sn", "hy", "ig", "it", "yi", "hi", "su", "id", "jw", "en", 
            "yo", "vi", "zh-TW", "zh-CN", 
        );
        if (!isset($tranInfo['tl']) || !in_array($tranInfo['tl'], $langArr)) {
            $tranInfo['tl'] = 'en';
        }

        if (!isset($tranInfo['text'])) {
            return false;
        }

        $text = (array) $tranInfo['text'];
        $tkk = $this->TKK();
        $urlInfo = [];
        foreach ($text as $key => $value) {
            $tk = $this->tk($value, $tkk);

            $urlInfo[$key] = array(
                'url' => "https://translate.google.cn/translate_a/single",
                'params' => array(
                    'client' => "t",
                    'sl' => "auto",
                    'tl' => $tranInfo['tl'],
                    'dt' => array(
                        "at", "bd", "ex", "ld", "md",
                        "qca", "rw", "rm", "ss", "t",
                    ),
                    'tk' => $tk,
                    'q' => urlencode($value),
                ),
            );
        }

        if (count($text) == 1) {
            $html = $this->curl(reset($urlInfo));
            $data = json_decode($html);
        } else {
            $html = curlMulti($urlInfo);
            $data = array_map("json_decode", $html);
        }

        if ($status) {
            return $data;
        } else {
            if (count($text) == 1) {
                $str = "";
                if (isset($data[0])) {
                    foreach ($data[0] as $row) {
                        $str .= $row[0];
                    }
                }
                
                return $str;
            } else {
                foreach ($data as $key => $row) {
                    $str = "";
                    if (isset($row[0])) {
                        foreach ($row[0] as $r) {
                            $str .= $r[0];
                        }
                    }

                    $data[$key] = $str;
                }

                return $data;
            }
        }
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