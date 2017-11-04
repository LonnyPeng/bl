<?php

namespace App\Controller;

use EasyWeChat\Foundation\Application;
use Framework\View\Model\JsonModel;
use Framework\View\Model\ViewModel;
use Framework\Utils\Http;

class ShopController extends AbstractActionController
{
	public $districtId = null;
	public $customerId = null;
	private $app = null;
	private $AK = "i5i0RY6UAnlPko8uf668dmOd";
	private $API = array(
		'key' => "YC3BZ-QUT6X-LB34N-74GMJ-CM3Z2-S7BFI",
		'referer' => "MapAPI",
	);

	public function init()
	{
	    parent::init();

	    $this->districtId = $_SESSION['customer_info']['district_id'];
        $this->customerId = $this->locator->get('Profile')['customer_id'];

        require_once VENDOR_DIR . 'autoload.php';

        $this->app = new Application(require_once CONFIG_DIR . 'wechat.config.php');

        $this->layout->title = '搜索取货点';
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

		$form = array('lat' => $lat, 'lng' => $lng);

		$urlInfo = array(
			'url' => "http://api.map.baidu.com/geocoder/v2/",
			'params' => array(
				'ak' => $this->AK,
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
			return JsonModel::init('ok', '', array('address' => $formattedAddress, 'form' => $form));
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

		$sql = "SELECT " . implode(",", $field) . " FROM t_shops s
				LEFT JOIN t_product_quantity pq ON pq.shop_id = s.shop_id
				WHERE " . implode(" AND ", $where) . " 
				ORDER BY distance ASC
				LIMIT 0, 3";
		$shopList = $this->locator->db->getAll($sql, $this->districtId);
		
		// print_r($shopList);die;
		return array(
			'formattedAddress' => $formattedAddress,
			'shopList' => $shopList,
			'js' => $this->app->js,
			'form' => $form,
		);
	}

	public function searchAction()
	{
		$id = trim($this->param('order_id'));
		$where = sprintf("order_id = %d", $id);
		$info = $this->models->order->getOrderInfo($where);
		if (!$info) {
		    $this->funcs->redirect($this->helpers->url('default/index'));
		}

		$address = trim($this->param('address_name'));
		if (!$address) {
		    $this->funcs->redirect($this->helpers->url('shop/index', array('order_id' => $id)));
		}

		$urlInfo = array(
			'url' => "http://api.map.baidu.com/geocoder/v2/",
			'params' => array(
				'output' => "js",
				'address' => $address,
				'output' => 'json',
				'ak' => $this->AK,
			),
		);
		$result = $this->funcs->curl($urlInfo);
		$result = json_decode($result, true);
		$result = $result['result']['location'];
		$lat = $result['lat'];
		$lng = $result['lng'];
		$form = array('lat' => $lat, 'lng' => $lng);

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

		$sql = "SELECT " . implode(",", $field) . " FROM t_shops s 
				LEFT JOIN t_product_quantity pq ON pq.shop_id = s.shop_id
				WHERE " . implode(" AND ", $where) . " 
				ORDER BY distance ASC
				LIMIT 0, 5";
		$shopList = $this->locator->db->getAll($sql, $this->districtId);

		// print_r($shopList);die;
		return array(
			'shopList' => $shopList,
			'form' => $form,
		);
	}

	public function mapAction()
	{
		$data = array(
			'eword' => trim($this->param('to-address')),
			'epointx' => sprintf("%.6f", trim($this->param('to-lng'))),
			'epointy' => sprintf("%.6f", trim($this->param('to-lat'))),
			'sword' => trim($this->param('form-address')),
			'spointx' => sprintf("%.6f", trim($this->param('form-lng'))),
			'spointy' => sprintf("%.6f", trim($this->param('form-lat'))),
			'footdetail' => "2",
			'topbar' => "1",
			'transport' => "0",
			'editstartbutton' => "1",
			'transmenu' => "",
			'positionbutton' => "0",
			'zoombutton' => "1",
			'trafficbutton' => '0',
			'coordtype' => '3',
		);
		$path = "";
		foreach ($data as $key => $value) {
			if ($path) {
				$path .= "&";
			}
			$path .= $key . "=" . $value;
		}

		$urlInfo = array(
			'url' => "http://apis.map.qq.com/tools/routeplan/" . $path,
			'params' => array(
				'key' => $this->API['key'],
				'referer' => $this->API['referer'],
				'back' => "0",
				'backurl' => trim($this->param('back-url')),
			),
		);

		$this->funcs->redirect($this->funcs->urlInit($urlInfo));

		return false;
	}

	public function loginAction()
	{
		if (isset($_SESSION['shop_login_id']) && isset($_SESSION['shop_login_name'])) {
		    $this->funcs->redirect($this->helpers->url('shop-admin/index'));
		}
		
	    $this->layout->title = '商户端登录';

	    if ($this->funcs->isAjax()) {
	    	$userName = trim($_POST['suser_name']);
	    	$userPassword = trim($_POST['suser_password']);

	    	$sql = "SELECT * FROM t_shop_users WHERE suser_name = ?";
	    	$info = $this->locator->db->getRow($sql, $userName);
	    	if (!$info) {
	    	    return new JsonModel('error', '用户名错误');
	    	}
	    	if (!$this->password->validate($userPassword, $info['suser_password'])) {
	    	    return new JsonModel('error', '密码错误');
	    	}

	    	$_SESSION['shop_login_id'] = intval($info['suser_id']);
	    	$_SESSION['shop_login_name'] = $info['suser_name'];

	    	// record login time
	    	$sql = "UPDATE t_shop_users
	    	        SET suser_logtime = NOW(),
	    	            suser_logip = ?,
	    	            suser_lognum = suser_lognum + 1
	    	        WHERE suser_id = ?";
	    	$this->locator->db->exec($sql, Http::getIp(), $info['suser_id']);

	    	// get redirect url
	    	if ($_POST['redirect']) {
	    	    $redirect = $_POST['redirect'];
	    	} else {
	    		$redirect = $this->helpers->url('shop-admin/index');
	    	}
	    	$model = new JsonModel('ok');
	    	$model->setRedirect($redirect);
	    	return $model;
	    }

	    return array();
	}
}