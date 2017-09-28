<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class ProductController extends AbstractActionController
{
	protected $imgType = array('image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png');
	protected $imgMaxSize = 1024 * 1024 * 4; //4M

	public function init() {
	    parent::init();
	}

	public function listAction()
	{
		$where = array();
		if ($this->param('product_name')) {
			$where[] = sprintf("p.product_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('product_name'))) . '%'));
		}
		if ($this->param('district_id')) {
			$where[] = sprintf("p.district_id = %d", trim($this->param('district_id')));
		}
		if ($this->param('attr_id')) {
			$where[] = sprintf("p.attr_id = %d", trim($this->param('attr_id')));
		}
		if ($this->param('product_quantity_min')) {
			$where[] = sprintf("p.product_quantity >= %d", trim($this->param('product_quantity_min')));
		}
		if ($this->param('product_quantity_max')) {
			$where[] = sprintf("p.product_quantity <= %d", trim($this->param('product_quantity_max')));
		}
		if ($this->param('product_price_min')) {
			$where[] = sprintf("p.product_price >= %.2f", trim($this->param('product_price_min')));
		}
		if ($this->param('product_price_max')) {
			$where[] = sprintf("p.product_price <= %.2f", trim($this->param('product_price_max')));
		}
		if ($this->param('product_start')) {
			$where[] = sprintf("p.product_start >= %s", date("Y-m-d 00:00:00", strtotime(trim($this->param('product_start')))));
		}
		if ($this->param('product_end')) {
			$where[] = sprintf("p.product_end <= %s", date("Y-m-d 23:59:59", strtotime(trim($this->param('product_end')))));
		}
		if ($this->param('product_status') === '0' || $this->param('product_status') === '1') {
			$where[] = sprintf("p.product_status = %d", $this->param('product_status'));
		}

		$count = $this->models->product->getCount(array('setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = array('p.*', 'd.district_name', 'pa.attr_name');
		$join = array(
			'LEFT JOIN t_district d ON p.district_id = d.district_id',
			'LEFT JOIN t_product_attr pa ON p.attr_id = pa.attr_id',
		);
		$sqlInfo = array(
			'setJoin' => $join,
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'district_id ASC, attr_id ASC, product_sort DESC, product_id DESC',
		);

		$productList = $this->models->product->getProduct($files, $sqlInfo);

		return array(
			'attrList' => $this->models->product->getAttrPair(),
			'districtList' => $this->models->district->getDistrictPair('district_status = 1'),
			'productList' => $productList,
		);
	}

	public function editAction()
	{
		$id = $this->param('id');
		$info = array();
		if ($id) {
			$info = $this->models->product->getProductById($id);
		} else {
			$sql = "SELECT MAX(product_code) FROM t_products";
			$maxCode = $this->locator->db->getOne($sql);
			if ($maxCode) {
			    $last6 = substr($maxCode, -6);
			    if (is_numeric($last6)) {
			        $tmpStr = 1000000 + $last6 + 1;
			        $newCode = 'SP' . substr($tmpStr, -6);
			    } else {
			        $newCode = 'SP000001';
			    }
			} else {
			    $newCode = "SP000001";
			}

			$info['product_code'] = $newCode;
		}

		if ($this->funcs->isAjax()) {
			$delImage = array();
			if (isset($_FILES['image'])) {
				foreach ($_FILES['image']['name'] as $key => $row) {
					foreach ($row as $ke => $value) {
						if (!$value) {
							continue;
						}

						if (in_array($key, array('home', 'logo'))) {
							if (isset($_POST['image'][$key][$ke])) {
								$imageId = $_POST['image'][$key][$ke];
								$sql = "SELECT image_path 
										FROM t_product_images 
										WHERE image_id = ?";
								$delImage[] = $this->locator->db->getOne($sql, $imageId);
							}
						}

						if (!in_array($_FILES['image']['type'][$key][$ke], $this->imgType)) {
							return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
						} elseif ($_FILES['image']['size'][$key][$ke] > $this->imgMaxSize) {
							return new JsonModel('error', '请选择小于4M的图片');
						}
					}
				}
			}

			if ($id) {
				if (!isset($_FILES['image']['name']['banner']) && !isset($_POST['image']['banner'])) {
					return new JsonModel('error', '请上传商品图片');
				}

				if (!isset($_FILES['image']['name']['detail']) && !isset($_POST['image']['detail'])) {
					return new JsonModel('error', '请上传图文详情');
				}
			} else {
				if (!isset($_FILES['image']['name']['home'])) {
					return new JsonModel('error', '请上传商品缩略图');
				}
				if (!isset($_FILES['image']['name']['logo'])) {
					return new JsonModel('error', '请上传品牌Logo');
				}

				if (!isset($_FILES['image']['name']['banner'])) {
					return new JsonModel('error', '请上传商品图片');
				}

				if (!isset($_FILES['image']['name']['detail'])) {
					return new JsonModel('error', '请上传图文详情');
				}
			}

			if (!$_POST['product_logo_name']) {
				return new JsonModel('error', '品牌名称不能为空');
			}
			if (!$_POST['product_name']) {
				return new JsonModel('error', '商品名不能为空');
			}
			if ($_POST['product_quantity'] < 1) {
				return new JsonModel('error', '商品库存数量不能小于1');
			}
			if (!$_POST['district_id']) {
				return new JsonModel('error', '请选择商品所在城市');
			}
			if ($_POST['product_price'] < 0) {
				return new JsonModel('error', '商品现价不能小于0');
			}
			if ($_POST['product_virtual_price'] < 0) {
				return new JsonModel('error', '商品原价不能小于0');
			}
			if (!$_POST['attr_id']) {
				return new JsonModel('error', '请选择商品类别');
			}
			if (!$_POST['product_end']) {
				return new JsonModel('error', '请选择商品结束时间');
			} elseif (time() >= strtotime($_POST['product_end'])) {
				return new JsonModel('error', '商品结束时间不能小于当前时间');
			}
			if ($_POST['product_qr_code_day'] < 1) {
				return new JsonModel('error', '领取二维码有效时长不能小于1天');
			}
			if (!$_POST['product_type']) {
				return new JsonModel('error', '领取方式');
			} elseif ($_POST['product_type'] == 2) {
				if ($_POST['product_group_num'] < 1) {
					return new JsonModel('error', '组团人数不能小于1');
				}
				if ($_POST['product_group_time'] < 1) {
					return new JsonModel('error', '组团过期时间不能小于1（天）');
				}
			} else {
				$_POST['product_group_num'] = $_POST['product_group_time'] = 0;
			}
			if (!$_POST['product_desc']) {
				return new JsonModel('error', '请填写推荐说明');
			}
			
			if (isset($_POST['quantity_num'])) {
				foreach ($_POST['quantity_num'] as $value) {
					if ($value < 0) {
						return new JsonModel('error', '库存分配数量不能小于0');
					}
				}

				if ($_POST['product_quantity'] < array_sum($_POST['quantity_num'])) {
					return new JsonModel('error', sprintf('库存分配数量总和不能大于于%d', $_POST['product_quantity']));
				}
			}

			//保存商品信息
			$map = array(
				'district_id' => $_POST['district_id'],
				'product_name' => trim($_POST['product_name']),
				'product_logo_name' => trim($_POST['product_logo_name']),
				'product_quantity' => trim($_POST['product_quantity']),
				'product_price' => trim($_POST['product_price']),
				'product_virtual_price' => trim($_POST['product_virtual_price']),
				'attr_id' => trim($_POST['attr_id']),
				'product_type' => trim($_POST['product_type']),
				'product_group_num' => trim($_POST['product_group_num']),
				'product_group_time' => trim($_POST['product_group_time']),
				'product_shaping_status' => trim($_POST['product_shaping_status']),
				'product_status' => trim($_POST['product_status']),
				'product_desc' => trim($_POST['product_desc']),
				'product_end' => date("Y-m-d 23:59:59", strtotime(trim($_POST['product_end']))),
				'product_qr_code_day' => trim($_POST['product_qr_code_day']),
				'product_sort' => $_POST['product_sort'] ?: '0',
			);
			$set = "district_id = :district_id,
					product_name = :product_name,
					product_logo_name = :product_logo_name,
					product_quantity = :product_quantity,
					product_price = :product_price,
					product_virtual_price = :product_virtual_price,
					attr_id = :attr_id,
					product_type = :product_type,
					product_group_num = :product_group_num,
					product_group_time = :product_group_time,
					product_shaping_status = :product_shaping_status,
					product_status = :product_status,
					product_desc = :product_desc,
					product_end = :product_end,
					product_qr_code_day = :product_qr_code_day,
					product_sort = :product_sort";
			if (!$id) {
				$map['product_code'] = $newCode;
				$map['product_start'] = date("Y-m-d 00:00:00", strtotime(trim($_POST['product_start'])));
				$set .= ",product_code = :product_code,product_start = :product_start";

				$sql = "INSERT INTO t_products SET $set";
			} else {
				$map['product_id'] = $id;
				$sql = "UPDATE t_products SET $set WHERE product_id = :product_id";
			}
			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				if (!$id) {
					$id = $this->locator->db->lastInsertId();
				}
			} else {
				return new JsonModel('error', '商品保存失败');
			}

			//保存库存分配
			$sql = "SELECT quantity_id FROM t_product_quantity WHERE product_id = ?";
			$quantityIds = (array) $this->locator->db->getColumn($sql, $id);

			$insertSql = "INSERT INTO t_product_quantity 
					SET product_id = :product_id, 
					shop_id = :shop_id, 
					quantity_num = :quantity_num";
			$updateSql = "UPDATE t_product_quantity 
					SET shop_id = :shop_id, 
					quantity_num = :quantity_num
					WHERE quantity_id = :quantity_id";
			if (isset($_POST['shop_id'])) {
				foreach ($_POST['shop_id'] as $key => $value) {
					if ($_POST['quantity_id'][$key]) {
						unset($quantityIds[array_search($_POST['quantity_id'][$key], $quantityIds)]);

						$this->locator->db->exec($updateSql, array(
							'shop_id' => $value,
							'quantity_num' => $_POST['quantity_num'][$key],
							'quantity_id' => $_POST['quantity_id'][$key],
						));
					} else {
						$this->locator->db->exec($insertSql, array(
							'product_id' => $id,
							'shop_id' => $value,
							'quantity_num' => $_POST['quantity_num'][$key],
						));
					}
				}

				if ($quantityIds) {
					$sql = "DELETE FROM t_product_quantity WHERE quantity_id IN (%s)";
					$sql = sprintf($sql, implode(",", $quantityIds));
					$this->locator->db->exec($sql);
				}
			} else {
				$sql = "DELETE FROM t_product_quantity WHERE product_id = ?";
				$this->locator->db->exec($sql, $id);
			}

			//保存图片
			if (isset($_FILES['image'])) {
				$images = $this->saveProductImg($_FILES['image']);
				$sql = "INSERT INTO t_product_images 
						SET product_id = :product_id, 
						image_path = :image_path, 
						image_type = :image_type";
				foreach ($images as $row) {
					$this->locator->db->exec($sql, array(
						'product_id' => $id,
						'image_path' => $row['image_path'],
						'image_type' => $row['image_type'],
					));
				}
			}

			if ($delImage) {
				foreach ($delImage as $value) {
					$this->delImage(PRODUCT_DIR . $value);
				}
			}
			return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('product/list'));
		}

		return array(
			'info' => $info,
			'attrList' => $this->models->product->getAttrPair(),
			'districtList' => $this->models->district->getDistrictPair('district_status = 1'),
			'shopPair' => $this->models->shop->getShopGoupByDistrict(),
		);
	}

	public function productEditSortAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sort = $this->param('sort');

		$sql = "UPDATE t_products 
				SET product_sort = :product_sort 
				WHERE product_id = :product_id";
		$status = $this->locator->db->exec($sql, array(
			'product_sort' => $sort,
			'product_id' => $id,
		));
		if ($status) {
			return JsonModel::init('ok', '成功')->setRedirect('reload');
		} else {
			return new JsonModel('error', '失败');
		}
	}

	public function deleteImgAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		
		$sql = "UPDATE t_product_images 
				SET image_status = 0 
				WHERE image_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			return JsonModel::init('ok', '成功');
		} else {
			return new JsonModel('error', '失败');
		}
	}

	public function setImageSortAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$data = $_POST['data'];
		$sql = "UPDATE t_product_images 
				SET image_sort = :sort 
				WHERE image_id = :id";
		foreach ($data as $row) {
			$this->locator->db->exec($sql, $row);
		}

		return JsonModel::init('ok', '')->setRedirect('reload');
	}

	protected function saveProductImg($files = array())
	{
		$dir = PRODUCT_DIR;
		$data = array();
		foreach ($files['name'] as $key => $row) {
			foreach ($row as $ke => $value) {
				$filename = $files['tmp_name'][$key][$ke];
				$upFileName = date('Ymd') . '/';
				$this->funcs->makeFile($dir . $upFileName);

				$ext = pathinfo($value, PATHINFO_EXTENSION);
				$fileName = md5($value . $this->funcs->rand());
				for($i=0;; $i++) {
					if (file_exists($dir . $upFileName . $fileName . '.' . $ext)) {
						$fileName = md5($value . $this->funcs->rand());
					} else {
						break;
					}
				}

				$uploadFile = $upFileName . $fileName . '.' . $ext;
				$uploadFilePath = $dir . $uploadFile;
				if (move_uploaded_file($files['tmp_name'][$key][$ke], $uploadFilePath)) {
					$data[] = array(
						'image_path' => $uploadFile,
						'image_type' => $key,
					);
				}
			}
		}

		return $data;
	}

	public function attrListAction()
	{
		$sql = "SELECT * FROM t_product_attr 
				ORDER BY attr_status DESC, attr_sort DESC, attr_id DESC";
		$attrList = $this->locator->db->getAll($sql);

		return array(
			'attrList' => $attrList,
		);
	}

	public function attrEditAction()
	{
		$id = $this->param('id');
		$info = array();
		if ($id) {
			$sql = "SELECT * FROM t_product_attr WHERE attr_id = ?";
			$info = $this->locator->db->getRow($sql, $id);
		}

		if ($this->funcs->isAjax()) {
			if (!$_POST['attr_name']) {
				return new JsonModel('error', '请输入类别名称');
			}

			$map = array(
				'attr_name' => trim($_POST['attr_name']),
				'attr_sort' => (int) trim($_POST['attr_sort']),
				'attr_status' => $_POST['attr_status'] ? 1 : 0,
			);
			$set = "attr_name = :attr_name,
					attr_sort = :attr_sort,
					attr_status = :attr_status";

			if (!$id) {
				$sql = "INSERT INTO t_product_attr SET $set";
			} else {
				$map['attr_id'] = $id;
				
				$sql = "UPDATE t_product_attr SET $set
						WHERE attr_id = :attr_id";
			}
			
			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('product/attr-list'));
			} else {
				return new JsonModel('error', '失败');
			}
		}

		return array(
			'info' => $info,
		);
	}

	public function attrEditSortAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sort = $this->param('sort');

		$sql = "UPDATE t_product_attr 
				SET attr_sort = :attr_sort 
				WHERE attr_id = :attr_id";
		$status = $this->locator->db->exec($sql, array(
			'attr_sort' => $sort,
			'attr_id' => $id,
		));
		if ($status) {
			return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('product/attr-list'));
		} else {
			return new JsonModel('error', '失败');
		}
	}

	public function attrDelAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');

		$sql = "SELECT COUNT(*) FROM t_products WHERE attr_id = ?";
		$productCount = $this->locator->db->getOne($sql, $id);
		if ($productCount > 0) {
			return new JsonModel('error', '请确保没有该类型的商品');
		}
		
		$sql = "DELETE FROM t_product_attr WHERE attr_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			return JsonModel::init('ok', '成功');
		} else {
			return new JsonModel('error', '失败');
		}
	}

	protected function delImage($path = '')
	{
		if (file_exists($path)) {
			@unlink($path);
		}
	}

	public function hotListAction()
	{
		$districtId = $this->param('district_id');
		if (!$districtId) {
			$sql = "SELECT district_id FROM t_district WHERE district_name = ?";
			$districtId = $this->locator->db->getOne($sql, '上海市');
			if ($districtId) {
				$this->funcs->redirect($this->helpers->url('product/hot-list', array('district_id' => $districtId)));
			} else {
				$this->funcs->redirect($this->helpers->url('default/index'));
			}
		}

		$sql = "SELECT h.*, p.product_code, p.product_name FROM t_hot_products h
				LEFT JOIN t_products p ON p.product_id = h.product_id
				WHERE h.district_id = ? 
				ORDER BY hot_type ASC, hot_id DESC";
		$hotList = $this->locator->db->getAll($sql, $districtId);

		return array(
			'hotList' => $hotList,
			'districtList' => $this->models->district->getDistrictPair('district_status = 1'),
		);
	}

	public function hotEditAction()
	{
		$id = $this->param('id');
		$districtId = $this->param('district_id');

		$info = array();
		if ($id) {
			$sql = "SELECT h.*, p.product_code 
					FROM t_hot_products h 
					LEFT JOIN t_products p ON p.product_id = h.product_id
					WHERE hot_id = ?";
			$info = $this->locator->db->getRow($sql, $id);
		}

		if ($this->funcs->isAjax()) {
			if (!$_POST['product_code']) {
				return new JsonModel('error', '请输入商品CODE');
			}

			$sql = "SELECT product_id FROM t_products WHERE product_code = ?";
			$productId = $this->locator->db->getOne($sql, trim($_POST['product_code']));
			if (!$productId) {
				return new JsonModel('error', '请输入商品CODE不存在');
			}

			if (!$_POST['hot_type']) {
				return new JsonModel('error', '请输选择推荐类型');
			}

			$map = array(
				'product_id' => $productId,
				'hot_type' => trim($_POST['hot_type']) == 'main' ?: 'minor',
			);
			$set = "product_id = :product_id,
					hot_type = :hot_type";

			if (!$id) {
				$map['district_id'] = $districtId;
				$set .= ",district_id = :district_id";

				$sql = "INSERT INTO t_hot_products SET $set";
			} else {
				$map['hot_id'] = $id;
				
				$sql = "UPDATE t_hot_products SET $set
						WHERE hot_id = :hot_id";
			}

			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('product/hot-list'));
			} else {
				return new JsonModel('error', '失败');
			}
		}
		return array(
			'info' => $info,
		);
	}

	public function hotDelAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "DELETE FROM t_hot_products WHERE hot_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}
}