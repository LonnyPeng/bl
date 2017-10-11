<?php

namespace App\Controller;

use EasyWeChat\Foundation\Application;
use Framework\View\Model\JsonModel;
use Framework\View\Model\ViewModel;

class ShopController extends AbstractActionController
{
	public $districtId = null;
	public $customerId = null;
	private $app = null;

	public function init()
	{
	    parent::init();

	    $this->districtId = $_SESSION['customer_info']['district_id'];
        $this->customerId = $this->locator->get('Profile')['customer_id'];

        require_once VENDOR_DIR . 'autoload.php';

        $this->app = new Application(require_once CONFIG_DIR . 'wechat.config.php');
	}

	public function indexAction()
	{
		$lat = $this->locator->get('Profile')['lat'];
		$lng = $this->locator->get('Profile')['lng'];
		
		// print_r($this->locator->get('Profile'));die;
		return array(
			'js' => $this->app->js,
		);
	}

	public function searchAction()
	{
		

		// print_r($shopList);die;
		return array();
	}
}