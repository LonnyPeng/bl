<?php

namespace App\Controller;

use Framework\Controller\AbstractActionController as ActionController;

class MonitorController extends ActionController
{
	public function indexAction()
	{
		$locator = $this->locator;
		$cache = $locator->get('Framework\Cache\Redis');
		$members = $cache->keys("member_[\d]{1,}");

		print_r($members);
		return false;
	}
}