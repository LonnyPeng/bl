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

    public function showId($str = '', $length = 6, $key = 0)
    {
        return str_pad($str, $length, $key, STR_PAD_LEFT);
    }

    /**
     * Generate a random password
     *
     * @param $length
     *
     * @return string
     */
    function randPassword($length = '') {
        $length = (int) $length;
        if ($length < 8) {
            $length = mt_rand(8, 32);
        }

        $data = array(
            'n' => ceil($length * 0.3),
            'l' => ceil($length * 0.4),
            'u' => ceil($length * 0.1),
        );
        $o = $length - $data['n'] - $data['l'] - $data['u'];
        if ($o) {
            $data['o'] = $o;
        } else {
            $data['l'] -= 1;
            $data['o'] = 1;
        }

        $str = "";
        for ($i=0; $i<$length; $i++) {
            foreach ($data as $key => $value) {
                if ($value <= 0) {
                    unset($data[$key]);
                }
            }

            $n = chr(mt_rand(48, 57));
            $l = chr(mt_rand(97, 122));
            $u = chr(mt_rand(65, 90));

            $oArr = array(
                mt_rand(33, 47), mt_rand(58, 64), 
                mt_rand(92, 96), mt_rand(123, 125),
            );
            $o = chr($oArr[array_rand($oArr, 1)]);

            $ke = array_rand($data, 1);

            $str .= $$ke;
            $data[$ke] -= 1;
        }

        return $str;
    }

    /**
     * Show time
     * @param int $time
     * @return string
     */
    public function showTime($time) 
    {
        $time = (int) trim($time);
        if ($time < 0) {
            $time = 0;
        }

        $init = [
            'year' => [31536000, '年'],
            'month' => [2592000, '月'],
            'day' => [86400, '天'],
            'hour' => [3600, '小时'],
            'minute' => [60, '分'],
            'second' => [1, '秒'],
        ];

        $str = '';
        foreach ($init as $key => $row) {
            $num = floor($time / $row[0]);
            if (in_array($key, array('year', 'month', 'day')) && !$num) {
                continue;
            }
            
            $num = str_pad($num, 2, 0, STR_PAD_LEFT);
            $str .= $num . $row[1];
            $time -= $num * $row[0];
        }

        return $str ?: '00秒';
    }

    public function isMobile()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $is_pc = (strpos($agent, 'windows nt')) ? true : false;
        $is_mac = (strpos($agent, 'mac os')) ? true : false;
        $is_iphone = (strpos($agent, 'iphone')) ? true : false;
        $is_android = (strpos($agent, 'android')) ? true : false;
        $is_ipad = (strpos($agent, 'ipad')) ? true : false;
        

        if($is_pc) {
            return  false;
        }
        
        if($is_mac) {
            return  true;
        }
        
        if($is_iphone) {
            return  true;
        }
        
        if($is_android) {
            return  true;
        }
        
        if($is_ipad) {
            return  true;
        }
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

        if (!file_exists($path)) {
            return array('status' => false, 'content' => '图片保存失败');
        }

        $pathinfo = getimagesize($path);
        if (!in_array($pathinfo['mime'], array('image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png'))) {
            return array('status' => false, 'content' => '请上传图片类型为 JPG, JPEG, PNG');
        }

        if (in_array($pathinfo['mime'], array('image/x-png', 'image/png'))) {
            $key = array('imagecreatefrompng', 'imagepng');
        } elseif (in_array($pathinfo['mime'], array('image/gif'))) {
            $key = array('imagecreatefromgif', 'imagegif');
        } else {
            $key = array('imagecreatefromjpeg', 'imagejpeg');
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
                $height = $pathinfo[1] * $insInit['width'] / $pathinfo[0];
            } else {
                $width = $pathinfo[0] * $insInit['height'] / $pathinfo[1];
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

    public function setLength($length = 0)
    {
        if ($length < 500) {
            return sprintf("%sm", $length);
        } else {
            $length /= 1000;
            return sprintf("%.1f公里", $length);
        }
    }

    /**
     * 
     * @param  [type] $string    [需要加密解密的字符串]
     * @param  [type] $operation [判断是加密还是解密，E表示加密，D表示解密]
     * @param  string $key       [密匙]
     * @return [type]            [description]
     */
    public function encrypt($string, $operation, $key='')
    {
        $map = array(
            '+' => '&#43;',
            '/' => '&#47;',
        );

        foreach ($map as $key => $value) {
            $string = str_replace($value, $key, $string);
        }

        $key=md5($key);
        $key_length=strlen($key); 
        $string=$operation =='D'?base64_decode($string):substr(md5($string.$key),0,8).$string; 
        $string_length=strlen($string); 
        $rndkey=$box=array(); 
        $result='';

        for($i=0;$i<=255;$i++){ 
            $rndkey[$i]=ord($key[$i%$key_length]); 
            $box[$i]=$i; 
        }

        for($j=$i=0;$i<256;$i++){ 
            $j=($j+$box[$i]+$rndkey[$i])%256; 
            $tmp=$box[$i]; 
            $box[$i]=$box[$j]; 
            $box[$j]=$tmp; 
        }

        for($a=$j=$i=0;$i<$string_length;$i++){ 
            $a=($a+1)%256; 
            $j=($j+$box[$a])%256; 
            $tmp=$box[$a]; 
            $box[$a]=$box[$j]; 
            $box[$j]=$tmp; 
            $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256])); 
        } 

        if($operation == 'D') { 
            if(substr($result,0,8)==substr(md5(substr($result,8).$key), 0, 8)) {
                return substr($result,8); 
            } else { 
                return''; 
            } 
        } else {
            $result = base64_encode($result);
            foreach ($map as $key => $value) {
                $result = str_replace($key, $value, $result);
            }

            return str_replace(array("="), '', $result); 
        }                           
    }

    public function showValue($value)
    {
        $int = (int) $value;
        $float = sprintf("%.2f", $value);

        if ($int < $float) {
            return $float;
        } else {
            return $int ?: '0';
        }
    }

    /**
     * Set the URL
     * 
     * @param string $url = ""
     * @return array
     */
    public function getUrl($url = "")
    {
        $url = urldecode(trim($url));
        if (!$url) {
            return false;
        }

        $urlInfo = explode("?", $url);
        if (isset($urlInfo[0])) {
            $urlInfo['url'] = $urlInfo[0];
            unset($urlInfo[0]);
        }

        if (isset($urlInfo[1])) {
            $urlInfo['params'] = array();
            foreach (explode("&", $urlInfo[1]) as $value) {
                if ($value) {
                    $rows = explode("=", $value);
                    $urlInfo['params'][$rows[0]] = isset($rows[1]) ? $rows[1] : '';
                }
            }
            unset($urlInfo[1]);
        }

        return $urlInfo;
    }

    /**
     * Dispose of legitimate URLs
     * 
     * @param string $url = ""
     * @return string
     */
    public function urlInit($urlInfo = "") 
    {
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
        } else {
            $url = '';
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

        return $url;
    }

    public function arrayUnique($data = array())
    {
        foreach ($data as $key => $row){
            foreach ($row as $k => $value) {
                $row[$k] = $k . "=" . $value;
            }
            $data[$key] = implode('&', $row);
        }
        $data = array_unique($data);

        foreach ($data as $key => $row){
            $row = explode("&", $row);
            foreach ($row as $k => $value) {
                $value = explode("=", $value);
                $row[$value[0]] = $value[1];
                unset($row[$k]);
            }
            $data[$key] = $row;
        }

        return $data;
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