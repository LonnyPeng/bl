<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use Framework\View\Model\ViewModel;

class ShopController extends AbstractActionController
{
	protected $imgType = array('image/gif', 'image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png');
	protected $imgMaxSize = 1024 * 1024 * 4; //4M

	public function init()
	{
	    parent::init();
	}

	public function listAction()
	{
		$this->perm->check(PERM_READ);
		
		$where = array();
		if ($this->param('shop_name')) {
			$where[] = sprintf("s.shop_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('shop_name'))) . '%'));
		}
		if ($this->param('district_id')) {
			$where[] = sprintf("s.district_id = %d", $this->param('district_id'));
		}
		if ($this->param('read_status') === '0' || $this->param('read_status') === '1') {
			$where[] = sprintf("read_status = %d", $this->param('read_status'));
		}

		$count = $this->models->shop->getCount(array('setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = array('s.*', 'd.district_name', 'su.suser_name');
		$join = array(
			'LEFT JOIN t_district d ON s.district_id = d.district_id',
			'LEFT JOIN t_shop_users su ON su.shop_id = s.shop_id',
		);
		$sqlInfo = array(
			'setJoin' => $join,
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'shop_id DESC',
		);

		$shopList = $this->models->shop->getShop($files, $sqlInfo);

		// print_r($shopList);die;
		return array(
			'shopList' => $shopList,
			'districtList' => $this->models->district->getDistrictPair('district_status = 1'),
		);
	}

	public function editAction()
	{
		$id = $this->param('id');
		$info = array();
		if ($id) {
			$info = $this->models->shop->getShopById($id);
		}

		if ($this->funcs->isAjax()) {
			if (!$_POST['shop_name']) {
				return new JsonModel('error', '请输入商家名称');
			}

			if (!$id) {
				if (!isset($_FILES['file'])) {
					return new JsonModel('error', '请上传缩略图');
				} elseif (!in_array($_FILES['file']['type'], $this->imgType)) {
					return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
				} elseif ($_FILES['file']['size'] > $this->imgMaxSize) {
					return new JsonModel('error', '请选择小于4M的图片');
				}

				if (!$_POST['suser_name']) {
					return new JsonModel('error', '请输入后台登录用户名');
				}
				$sql = "SELECT COUNT(*) FROM t_shop_users WHERE suser_name = ?";
				if ($this->locator->db->getOne($sql, trim($_POST['suser_name']))) {
					return new JsonModel('error', '后台登录用户名已存在');
				}

				if (!$_POST['suser_password']) {
					return new JsonModel('error', '请输入后台登录密码');
				}
			} else {
				if (isset($_FILES['file'])) {
					if (!in_array($_FILES['file']['type'], $this->imgType)) {
						return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
					} elseif ($_FILES['file']['size'] > $this->imgMaxSize) {
						return new JsonModel('error', '请选择小于4M的图片');
					}
				}
			}

			if (!$_POST['district_id']) {
				return new JsonModel('error', '请选择商家所在城市');
			}
			if (!$_POST['shop_tel']) {
				return new JsonModel('error', '请输入联系电话');
			} elseif (!isPhone($_POST['shop_tel'])) {
				return new JsonModel('error', '电话格式错误');
			}
			if (!$_POST['shop_address']) {
				return new JsonModel('error', '请输入地址');
			}
			if (!$_POST['shop_lng']) {
				return new JsonModel('error', '请输入经度');
			} elseif ($_POST['shop_lng'] < -180 || $_POST['shop_lng'] > 180) {
				return new JsonModel('error', '请确保经度范围在（-180，180）');
			}
			if (!$_POST['shop_lat']) {
				return new JsonModel('error', '请输入纬度');
			} elseif ($_POST['shop_lat'] < -90 || $_POST['shop_lat'] > 90) {
				return new JsonModel('error', '请确保纬度范围在（-90，90）');
			}

			if (!$id) {
				$path = $this->saveShopImg($_FILES['file']);
				if (!$path) {
					return new JsonModel('error', '缩略图保存失败');
				} else {
					//处理图片
					$result = $this->funcs->setImage(SHOP_DIR . $path, SHOP_DIR, 70, 70);
					if (!$result['status']) {
						return new JsonModel('error', $result['content']);
					} else {
						$path = $result['content'];
					}
				}
			} else {
				if (isset($_FILES['file'])) {
					$path = $this->saveShopImg($_FILES['file']);
					if (!$path) {
						return new JsonModel('error', '缩略图保存失败');
					} else {
						//处理图片
						$result = $this->funcs->setImage(SHOP_DIR . $path, SHOP_DIR, 70, 70);
						if (!$result['status']) {
							return new JsonModel('error', $result['content']);
						} else {
							$path = $result['content'];
						}
					}
				}
			}

			$map = array(
				'shop_name' => trim($_POST['shop_name']),
				'district_id' => trim($_POST['district_id']),
				'shop_tel' => trim($_POST['shop_tel']),
				'shop_address' => trim($_POST['shop_address']),
				'shop_lng' => trim($_POST['shop_lng']),
				'shop_lat' => trim($_POST['shop_lat']),
				'shop_dec' => trim($_POST['shop_dec']),
				'shop_status' => $_POST['shop_status'] ? 1 : 0,
			);
			$set = "shop_name = :shop_name,
					district_id = :district_id,
					shop_tel = :shop_tel,
					shop_address = :shop_address,
					shop_lng = :shop_lng,
					shop_lat = :shop_lat,
					shop_dec = :shop_dec,
					shop_status = :shop_status";

			if (!$id) {
				$map['shop_headimg'] = $path;
				$set .= ",shop_headimg = :shop_headimg";

				$sql = "INSERT INTO t_shops SET $set";
			} else {
				$map['shop_id'] = $id;
				if (isset($_FILES['file'])) {
					$sql = "SELECT shop_headimg FROM t_shops WHERE shop_id = ?";
					$pathOld = $this->locator->db->getOne($sql, $id);

					$map['shop_headimg'] = $path;
					$set .= ",shop_headimg = :shop_headimg";
				}
				
				$sql = "UPDATE t_shops SET $set
						WHERE shop_id = :shop_id";
			}

			$status = $this->locator->db->exec($sql, $map);
			if (!$status) {
				return new JsonModel('error', '失败');
			}

			if (!$id) {
				//保存商户登录信息
				$sql = "INSERT INTO t_shop_users 
						SET shop_id = :shop_id, 
						suser_name = :suser_name, 
						suser_password = :suser_password";
				$this->locator->db->exec($sql, array(
					'shop_id' => $this->locator->db->lastInsertId(),
					'suser_name' => trim($_POST['suser_name']),
					'suser_password' => $this->password->encrypt(trim($_POST['suser_password'])),
				));
			}

			if ($id && isset($_FILES['file'])) {
				$this->delImage(SHOP_DIR . $pathOld);
			}
			return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('shop/list'));
		}
		return array(
			'info' => $info,
			'districtList' => $this->models->district->getDistrictPair('district_status = 1'),
		);
	}

	public function delAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "SELECT shop_headimg FROM t_shops WHERE shop_id = ?";
		$path = $this->locator->db->getOne($sql, $id);
		
		$sql = "DELETE FROM t_shops WHERE shop_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if (!$status) {
			return new JsonModel('error', '删除失败');
		}
		$sql = "DELETE FROM t_shop_users WHERE shop_id = ?";
		$status = $this->locator->db->exec($sql, $id);

		$this->delImage(SHOP_DIR . $path);
		return JsonModel::init('ok', '删除成功');
	}

	protected function saveShopImg($row = array())
	{
		$filename = $row['tmp_name'];
		$dir = SHOP_DIR;
		$upFileName = date('Ymd') . '/';
		$this->funcs->makeFile($dir . $upFileName);

		$ext = pathinfo($row['name'], PATHINFO_EXTENSION);
		$fileName = md5($row['name'] . $this->funcs->rand());
		for($i=0;; $i++) {
			if (file_exists($dir . $upFileName . $fileName . '.' . $ext)) {
				$fileName = md5($row['name'] . $this->funcs->rand());
			} else {
				break;
			}
		}

		$uploadFile = $upFileName . $fileName . '.' . $ext;
		$uploadFilePath = $dir . $uploadFile;
		if (move_uploaded_file($row['tmp_name'], $uploadFilePath)) {
			return $uploadFile;
		} else {
			return false;
		}
	}

	protected function delImage($path = '')
	{
		if (file_exists($path)) {
			@unlink($path);
		}
	}
}