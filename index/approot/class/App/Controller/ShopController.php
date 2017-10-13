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
		$id = trim($this->param('order_id'));
		$where = sprintf("order_id = %d", $id);
		$info = $this->models->order->getOrderInfo($where);
		if (!$info) {
		    $this->funcs->redirect($this->helpers->url('default/index'));
		}

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

		//获取最近取货点3个
		$field = array(
		    's.*, pq.quantity_num, pq.quantity_id',
		    sprintf("ROUND(
		        6378.137 * 1000 * 2 * ASIN(
		            SQRT(
		                POW(SIN((%s * PI() / 180 - s.shop_lat * PI() / 180) / 2), 2) + 
		                COS(%s * PI() / 180) * 
		                COS(s.shop_lat * PI() / 180) * 
		                POW(SIN((%s * PI() / 180 - s.shop_lng * PI() / 180) / 2), 2)
		            )
		        )
		    ) AS distance", $lat, $lat, $lng),
		);
		$where = array(
			's.shop_status = 1',
			sprintf("s.district_id = %d", $this->districtId),
			sprintf("pq.product_id = %d", $info['product_id']),
		);

		$sql = "SELECT " . implode(",", $field) . " FROM t_product_quantity pq 
				LEFT JOIN t_shops s ON pq.shop_id = s.shop_id
				WHERE " . implode(" AND ", $where) . " 
				ORDER BY distance ASC
				LIMIT 0, 3";
		$shopList = $this->locator->db->getAll($sql, $this->districtId);
		
		// print_r($shopList);die;
		return array(
			'formattedAddress' => $formattedAddress,
			'shopList' => $shopList,
			'js' => $this->app->js,
		);
	}

	public function searchAction()
	{
		

		// print_r($shopList);die;
		return array();
	}
}