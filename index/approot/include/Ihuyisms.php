<?php

namespace Ihuyi;

/**
* 
*/
class Sms
{
	public $template = array(
		'1' => "您的验证码是：%s。请不要把验证码泄露给其他人。",
	);
	private $account = null;
	private $password = null;

	public function __construct($config)
	{
	    $this->account = $config['account'];
	    $this->password = $config['password'];
	}

	public function send($row = array('template' => '', 'phone' => '', 'code' => ''))
	{
		$urlInfo = array(
			'url' => 'http://106.ihuyi.cn/webservice/sms.php?method=Submit',
			'params' => array(
				'account' => $this->account,
				'password' => $this->password,
				'mobile' => $row['phone'],
				'content' => sprintf($this->template[$row['template']], $row['code']),
			),
		);

		$result = $this->curl($urlInfo, 'POST');
		$result = $this->xml_to_array($result);
		$result = $result['SubmitResult'];

		return $result;
	}

	/**
	 * Use the curl virtual browser
	 *
	 * @param array $urlInfo = array('url' => "https://www.baidu.com/", 'params' => array('key' => 'test'), 'cookie' => 'cookie')
	 * @param string $type = 'GET|POST'
	 * @param boolean $info = false|true
	 * @return string|array
	 */
	public function curl($urlInfo, $type = "GET", $info = false) 
	{
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

	//将 xml数据转换为数组格式。
	public function xml_to_array($xml)
	{
		$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
			$count = count($matches[0]);
			for($i = 0; $i < $count; $i++){
			$subxml= $matches[2][$i];
			$key = $matches[1][$i];
				if(preg_match( $reg, $subxml )){
					$arr[$key] = $this->xml_to_array( $subxml );
				}else{
					$arr[$key] = $subxml;
				}
			}
		}

		return $arr;
	}
}