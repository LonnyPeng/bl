<?php

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

