<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use Framework\View\Model\ViewModel;

class ShopController extends AbstractActionController
{
	public $districtId = null;
	public $customerId = null;

	public function init()
	{
	    parent::init();

	    $this->districtId = $_SESSION['customer_info']['district_id'];
        $this->customerId = $this->locator->get('Profile')['customer_id'];
	}

	public function indexAction()
	{
		return array();
	}

	public function searchAction()
	{
		

		// print_r($shopList);die;
		return array();
	}
}