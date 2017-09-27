<?php
// //微信认证
define("TOKEN", "weixin");
if (isset($_GET["signature"]) && isset($_GET["timestamp"]) 
	&& isset($_GET["nonce"]) && isset($_GET["echostr"])) {
	$signature = $_GET["signature"];
	$timestamp = $_GET["timestamp"];
	$nonce = $_GET["nonce"];
	$token = TOKEN;

	$tmpArr = array($token, $timestamp, $nonce);
	sort($tmpArr, SORT_STRING);

	$tmpStr = implode($tmpArr);
	$tmpStr = sha1($tmpStr);

	if($tmpStr == $signature){
	    echo $_GET["echostr"];
	} else {
	    echo 'error';
	}
}
die;