<?php

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

	function isLogin()
	{
		return isset($_SESSION['shop_login_id']);
	}
}