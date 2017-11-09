<?php

namespace App\Controller;

use Framework\Controller\AbstractActionController as ActionController;

class MonitorController extends ActionController
{
	public function indexAction()
	{
		echo "123";
		return false;
	}
}