<?php

namespace App\Controller;

use Framework\Controller\AbstractActionController as ActionController;

class MonitorController extends ActionController
{
	public function indexAction()
	{
		$locator = $this->locator;
		$cache = $locator->get('Framework\Cache\Redis');
		$members = $cache->keys("member_*");

		foreach ($members as $key => $value) {
			$members[$key] = $cache->getInit($value);
		}

		$members = array_column($members, "member_name");

		print_r($members);
		return false;
	}
}