<?php

//微信认证
define("TOKEN", "bailin");
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

require_once 'init.inc.php';

session_start();

/* @var $locator \Framework\ServiceLocator\ServiceLocator */
$params = $locator->get('Params');
$controller = &$params['_controller'];
$action = &$params['_action'];

/* @var $front \Framework\Controller\FrontController */
$front = $locator->get('FrontController');
$result = $front->dispatch($controller, $action);
$front->run($result);

