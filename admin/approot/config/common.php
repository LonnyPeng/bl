<?php

if (!function_exists('isEmail')) {
	/**
	 * Check string if is email
	 * 
	 * @param string $email
	 * @return boolean
	 */
	function isEmail($email)
	{
	    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
	         return true;
	    } else {
	         return false;
	    }
	}
}

if (!function_exists('isUrl')) {
	/** 
	 * Check string if is url
	 * @param string  $url
	 * @return boolean
	 */  
	function isUrl($url)
	{  
	    if(filter_var($url,FILTER_VALIDATE_URL)) {  
	        return true;  
	    } else {  
	        return false;  
	    }  
	}
}

if (!function_exists('isIp')) {
	/** 
	 * Check string if is IP
	 * @param string  $ip
	 * @return boolean
	 */  
	function isIp($ip)
	{  
	    if(filter_var($url,FILTER_VALIDATE_IP)) {  
	        return true;  
	    } else {  
	        return false;  
	    }  
	}
}

if (!function_exists('isPhone')) {
	/** 
	 * Check string if is phone
	 * @param string  $phone
	 * @return boolean
	 */  
	function isPhone($phone)
	{
	    if(preg_match("/^((0\d{2,3}-\d{7,8})|(1[35847]\d{9}))$/", $phone)) {  
	        return true;  
	    } else {  
	        return false;  
	    }  
	}
}

if (!function_exists('setText')) {
	/**
	 * Encoding conversion
	 * 
	 * @param string $str = ""
	 * @return string $str
	 */
	function setText($str = "")
	{
	    $str = iconv("UTF-8", "GBK//IGNORE", trim($str));
	    $str .= "\t";

	    return $str;
	}
}

if (!function_exists('setImport')) {
	/**
	 * Encoding conversion
	 * 
	 * @param string $str = ""
	 * @return string $str
	 */
	function setImport($str = "")
	{
		$str = str_replace(chr(239) . chr(187) . chr(191), '', $str);
		$str = trim($str, '"');
		$str = iconv("GBK", "UTF-8//IGNORE", trim($str));

	    return $str;
	}
}

 