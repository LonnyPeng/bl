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
		if ($this->funcs->isAjax()) {
			$lat = $this->param('lat');
			$lng = $this->param('lng');
		} else {
			$lat = $this->locator->get('Profile')['lat'];
			$lng = $this->locator->get('Profile')['lng'];
		}

		$urlInfo = array(
			'url' => "http://api.map.baidu.com/geocoder/v2/",
			'params' => array(
				'ak' => "se0o5ZCif8WBlePtDwnpOmfL",
				'location' => $lat . "," . $lng,
				'output' => 'json',
				'pois' => '0',
			),
		);
		$result = $this->funcs->curl($urlInfo);
		$result = json_decode($result, true);
		if (isset($result['result']['formatted_address'])) {
			$formattedAddress = $result['result']['formatted_address'];
		} else {
			$formattedAddress = '';
		}

		if ($this->funcs->isAjax()) {
			return JsonModel::init('ok', '', array('address' => $formattedAddress));
		}
		
		// print_r($this->locator->get('Profile'));die;
		return array(
			'formattedAddress' => $formattedAddress,
			'js' => $this->app->js,
		);
	}

	public function searchAction()
	{
		

		// print_r($shopList);die;
		return array();
	}
}