<?php

namespace App\Controller;

use Framework\Utils\Http;
use Framework\View\Model\JsonModel;
use Framework\View\Model\ViewModel;

class BasicController extends AbstractActionController
{
	protected $configPage = array(
		'qdgz' => '签到规则',
		'fwxy' => '服务协议',
		'jfsm' => '积分说明',
		'yxgz' => '游戏规则',
	);

	protected $imgType = array('image/gif', 'image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png');
	protected $imgMaxSize = 1024 * 1024 * 4; //4M
	protected $videoType = array('video/mp4');
	protected $vioMaxSize = 1024 * 1024 * 10; //10M

	public function init()
	{
	    parent::init();
	}

	public function districtListAction()
	{
		$where = array();
		if ($this->param('district_name')) {
			$where[] = sprintf("d.district_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('district_name'))) . '%'));
		}
		if ($this->param('district_status') === '0' || $this->param('district_status') === '1') {
			$where[] = sprintf("d.district_status = %d", $this->param('district_status'));
		}

		$count = $this->models->district->getCount(array('setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = '*';
		$sqlInfo = array(
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'district_status DESC, CONVERT(d.district_name USING GBK) ASC',
		);

		$districtList = $this->models->district->getDistrict($files, $sqlInfo);

		// print_r($districtList);die;
	    return array(
	    	'districtList' => $districtList,
	    );
	}

	public function districtEditAction()
	{
		$districtId = $this->param('id');
		$districtInfo = array();
		if ($districtId) {
			$districtInfo = $this->models->district->getDistrictById($districtId);
		}

		if ($this->funcs->isAjax()) {
			$districtName = trim($_POST['district_name']);
			if (!$districtName) {
				return new JsonModel('error', '请输入城市名');
			}

			$map = array(
				'district_name' => trim($_POST['district_name']),
				'district_status' => $_POST['district_status'],
			);
			$set = "district_name = :district_name,
					district_status = :district_status";

			if (!$districtId) {
				$sql = "INSERT INTO t_district SET $set";
			} else {
				$map['district_id'] = $districtId;
				$sql = "UPDATE t_district SET $set
						WHERE district_id = :district_id";
			}

			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('basic/district-list'));
			} else {
				return new JsonModel('error', '失败');
			}
		}
		return array(
			'districtInfo' => $districtInfo,
		);
	}

	public function districtDelAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$districtId = $this->param('id');
		
		$sql = "DELETE FROM t_district WHERE district_id = ?";
		$status = $this->locator->db->exec($sql, $districtId);
		if ($status) {
			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}

	public function districtSearchAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$name = trim($this->param('q'));

		$field = 'district_name';
		$where = sprintf("district_name LIKE '%s'", addslashes('%' . $this->helpers->escape($name) . '%'));
		$sql = "SELECT district_name 
				FROM t_district 
				WHERE $where 
				ORDER BY CONVERT(district_name USING GBK) ASC 
				LIMIT 0, 20";
		$result = $this->locator->db->getColumn($sql);
		return JsonModel::init('ok', '', $result);
	}

	public function configListAction()
	{
		$sql = "SELECT * FROM t_configs ORDER BY config_id DESC";
		$configList = (array) $this->locator->db->getAll($sql);

		// print_r($configList);die;
	    return array(
	    	'configList' => $configList,
	    	'configPage' => $this->configPage,
	    );
	}

	public function configEditAction()
	{
		$id = $this->param('id');
		$sql = "SELECT * FROM t_configs WHERE config_id = ?";
		$info = $this->locator->db->getRow($sql, $id);
		if ($id && !$info) {
			$this->funcs->redirect($this->helpers->url('basic/config-list'));
		}

		if ($this->funcs->isAjax()) {
			$page = trim($_POST['config_page']);
			if (!$page) {
				return new JsonModel('error', '请选择页面');
			}

			if (!$id) {
				$sql = "SELECT COUNT(*) FROM t_configs WHERE config_page = ?";
				if ($this->locator->db->getOne($sql, $page)) {
					return new JsonModel('error', '该页面已存在');
				}
			}

			if (!$_POST['config_title']) {
				return new JsonModel('error', '请输入标题');
			}
			if (!$_POST['config_text']) {
				return new JsonModel('error', '请输入内容');
			}

			$map = array(
				'config_page' => $page,
				'config_title' => trim($_POST['config_title']),
				'config_text' => trim($_POST['config_text']),
			);
			$set = "config_page = :config_page,
					config_title = :config_title,
					config_text = :config_text";

			if (!$id) {
				$sql = "INSERT INTO t_configs SET $set";
			} else {
				$map['config_id'] = $id;
				$sql = "UPDATE t_configs SET $set
						WHERE config_id = :config_id";
			}

			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('basic/config-list'));
			} else {
				return new JsonModel('error', '失败');
			}
		}
		return array(
			'info' => $info,
			'configPage' => $this->configPage,
		);
	}

	public function configDelAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		
		$sql = "DELETE FROM t_configs WHERE config_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}

	public function homeImageAction()
	{
		$districtId = $this->param('district_id');
		if (!$districtId) {
			$sql = "SELECT district_id FROM t_district WHERE district_name = ?";
			$districtId = $this->locator->db->getOne($sql, '上海市');
			if ($districtId) {
				$this->funcs->redirect($this->helpers->url('basic/home-image', array('district_id' => $districtId)));
			} else {
				$this->funcs->redirect($this->helpers->url('default/index'));
			}
		}

		$sql = "SELECT * FROM t_images 
				WHERE district_id = ? 
				ORDER BY image_sort DESC, image_id DESC";
		$imageList = $this->locator->db->getAll($sql, $districtId);

		return array(
			'imageList' => $imageList,
			'districtList' => $this->models->district->getDistrictPair('district_status = 1'),
		);
	}

	public function homeImageEditAction()
	{
		$id = $this->param('id');
		$info = array();
		if ($id) {
			$sql = "SELECT * FROM t_images WHERE image_id = ?";
			$info = $this->locator->db->getRow($sql, $id);
		}

		if ($this->funcs->isAjax()) {
			if (!$id) {
				if (!isset($_FILES['file'])) {
					return new JsonModel('error', '请上传图片');
				} elseif (!in_array($_FILES['file']['type'], $this->imgType)) {
					return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
				} elseif ($_FILES['file']['size'] > $this->imgMaxSize) {
					return new JsonModel('error', '请选择小于4M的图片');
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
				return new JsonModel('error', '请选择城市');
			}

			$key = trim($_POST['image_href']);
			$imageHref = '';
			if (preg_match("/^((http)|(www\.))/i", $key)) {
				//链接
				$imageHref = $key;
			} elseif (preg_match("/^sp[\d]{6}/i", $key)) {
				//产品code
				$sql = "SELECT product_id FROM t_products WHERE product_code = ?";
				$productId = $this->locator->db->getOne($sql, $key);
				$imageHref = (string) $this->helpers->url('product/list', array('id' => $productId), true);
			} elseif ($key) {
				return new JsonModel('error', '链接错误');
			}

			if (!$id) {
				$path = $this->imageUpdate($_FILES['file']);
				if (!$path) {
					return new JsonModel('error', '图片保存失败');
				} else {
					//处理图片
					$result = $this->funcs->setImage(SYS_DIR . $path, SYS_DIR);
					if (!$result['status']) {
						return new JsonModel('error', $result['content']);
					} else {
						$path = $result['content'];
					}
				}
			} else {
				if (isset($_FILES['file'])) {
					$path = $this->imageUpdate($_FILES['file']);
					if (!$path) {
						return new JsonModel('error', '图片保存失败');
					} else {
						//处理图片
						$result = $this->funcs->setImage(SYS_DIR . $path, SYS_DIR);
						if (!$result['status']) {
							return new JsonModel('error', $result['content']);
						} else {
							$path = $result['content'];
						}
					}
				}
			}

			$map = array(
				'image_title' => trim($_POST['image_title']),
				'district_id' => trim($_POST['district_id']),
				'image_href' => $imageHref,
				'image_sort' => $_POST['image_sort'] ?: 0,
			);
			$set = "image_title = :image_title,
					district_id = :district_id,
					image_href = :image_href,
					image_sort = :image_sort";

			if (!$id) {
				$map['image_path'] = $path;
				$set .= ",image_path = :image_path";

				$sql = "INSERT INTO t_images SET $set";
			} else {
				$map['image_id'] = $id;
				if (isset($_FILES['file'])) {
					$sql = "SELECT image_path FROM t_images WHERE image_id = ?";
					$pathOld = $this->locator->db->getOne($sql, $id);

					$map['image_path'] = $path;
					$set .= ",image_path = :image_path";
				}
				
				$sql = "UPDATE t_images SET $set
						WHERE image_id = :image_id";
			}

			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				if ($id && isset($_FILES['file'])) {
					$this->delImage(SYS_DIR . $pathOld);
				}
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('basic/home-image'));
			} else {
				return new JsonModel('error', '失败');
			}
		}

		return array(
			'info' => $info,
			'districtList' => $this->models->district->getDistrictPair('district_status = 1'),
		);
	}

	public function imageUpdate($row = array())
	{
		$dir = SYS_DIR;

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
		move_uploaded_file($row['tmp_name'], $uploadFilePath);

		return $uploadFile;
	}

	public function delImageAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "SELECT image_path FROM t_images WHERE image_id = ?";
		$path = $this->locator->db->getOne($sql, $id);
		
		$sql = "DELETE FROM t_images WHERE image_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			$filename = SYS_DIR . $path;
			if (file_exists($filename)) {
				@unlink($filename);
			}

			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}

	public function homeRecommendAction()
	{
		$districtId = $this->param('district_id');
		if (!$districtId) {
			$sql = "SELECT district_id FROM t_district WHERE district_name = ?";
			$districtId = $this->locator->db->getOne($sql, '上海市');
			if ($districtId) {
				$this->funcs->redirect($this->helpers->url('basic/home-recommend', array('district_id' => $districtId)));
			} else {
				$this->funcs->redirect($this->helpers->url('default/index'));
			}
		}

		$sql = "SELECT * FROM t_recommends 
				WHERE district_id = ? 
				ORDER BY recommend_sort DESC, recommend_id DESC";
		$imageList = $this->locator->db->getAll($sql, $districtId);

		return array(
			'imageList' => $imageList,
			'districtList' => $this->models->district->getDistrictPair('district_status = 1'),
		);
	}

	public function homeRecommendEditAction()
	{
		$id = $this->param('id');
		$info = array();
		if ($id) {
			$sql = "SELECT * FROM t_recommends WHERE recommend_id = ?";
			$info = $this->locator->db->getRow($sql, $id);
		}

		if ($this->funcs->isAjax()) {
			if (!$_POST['recommend_title']) {
				return new JsonModel('error', '请输入标题');
			}

			if (!$id) {
				if (!isset($_FILES['recommend_path'])) {
					return new JsonModel('error', '请上传图片');
				} elseif (!in_array($_FILES['recommend_path']['type'], $this->imgType)) {
					return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
				} elseif ($_FILES['recommend_path']['size'] > $this->imgMaxSize) {
					return new JsonModel('error', '请选择小于4M的图片');
				}

				if (isset($_FILES['recommend_logo'])) {
					if (!in_array($_FILES['recommend_logo']['type'], $this->imgType)) {
						return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
					} elseif ($_FILES['recommend_logo']['size'] > $this->imgMaxSize) {
						return new JsonModel('error', '请选择小于4M的图片');
					}
				}
			} else {
				if (isset($_FILES['recommend_path'])) {
					if (!in_array($_FILES['recommend_path']['type'], $this->imgType)) {
						return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
					} elseif ($_FILES['recommend_path']['size'] > $this->imgMaxSize) {
						return new JsonModel('error', '请选择小于4M的图片');
					}
				}

				if (isset($_FILES['recommend_logo'])) {
					if (!in_array($_FILES['recommend_logo']['type'], $this->imgType)) {
						return new JsonModel('error', '请上传图片类型为 JPG, JPEG, PNG, GIF');
					} elseif ($_FILES['recommend_logo']['size'] > $this->imgMaxSize) {
						return new JsonModel('error', '请选择小于4M的图片');
					}
				}
			}

			if (!$_POST['district_id']) {
				return new JsonModel('error', '请选择城市');
			}

			$code = trim($_POST['product_code']);
			if ($code) {
				if (!preg_match("/^sp[\d]{6}/i", $key)) {
					return new JsonModel('error', '商品CODE错误');
				} 
			}

			$logo = '';
			if (!$id) {
				$path = $this->imageUpdate($_FILES['recommend_path']);
				if (!$path) {
					return new JsonModel('error', '图片保存失败');
				} else {
					//处理图片
					$result = $this->funcs->setImage(SYS_DIR . $path, SYS_DIR);
					if (!$result['status']) {
						return new JsonModel('error', $result['content']);
					} else {
						$path = $result['content'];
					}
				}

				if (isset($_FILES['recommend_logo'])) {
					$logo = $this->imageUpdate($_FILES['recommend_logo']);
				}
			} else {
				if (isset($_FILES['recommend_path'])) {
					$path = $this->imageUpdate($_FILES['recommend_path']);
					if (!$path) {
						return new JsonModel('error', '图片保存失败');
					} else {
						//处理图片
						$result = $this->funcs->setImage(SYS_DIR . $path, SYS_DIR);
						if (!$result['status']) {
							return new JsonModel('error', $result['content']);
						} else {
							$path = $result['content'];
						}
					}
				}

				if (isset($_FILES['recommend_logo'])) {
					$logo = $this->imageUpdate($_FILES['recommend_logo']);
				}
			}

			$map = array(
				'recommend_title' => trim($_POST['recommend_title']),
				'district_id' => trim($_POST['district_id']),
				'product_code' => $code,
				'recommend_sort' => $_POST['recommend_sort'] ?: 0,
			);
			$set = "recommend_title = :recommend_title,
					district_id = :district_id,
					product_code = :product_code,
					recommend_sort = :recommend_sort";

			if (!$id) {
				$map['recommend_path'] = $path;
				$map['recommend_logo'] = $logo;
				$set .= ",recommend_path = :recommend_path, recommend_logo = :recommend_logo";

				$sql = "INSERT INTO t_recommends SET $set";
			} else {
				$map['recommend_id'] = $id;
				if (isset($_FILES['recommend_path'])) {
					$sql = "SELECT recommend_path FROM t_recommends WHERE recommend_id = ?";
					$pathOld = $this->locator->db->getOne($sql, $id);

					$map['recommend_path'] = $path;
					$set .= ",recommend_path = :recommend_path";
				}
				if (isset($_FILES['recommend_logo'])) {
					$sql = "SELECT recommend_logo FROM t_recommends WHERE recommend_id = ?";
					$logoOld = $this->locator->db->getOne($sql, $id);

					$map['recommend_logo'] = $path;
					$set .= ",recommend_logo = :recommend_logo";
				}
				
				$sql = "UPDATE t_recommends SET $set
						WHERE recommend_id = :recommend_id";
			}

			$status = $this->locator->db->exec($sql, $map);
			if ($status) {
				if ($id && isset($_FILES['recommend_path'])) {
					$this->delImage(SYS_DIR . $pathOld);
				}
				if ($id && isset($_FILES['recommend_logo'])) {
					$this->delImage(SYS_DIR . $logoOld);
				}
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('basic/home-recommend'));
			} else {
				return new JsonModel('error', '失败');
			}
		}

		return array(
			'info' => $info,
			'districtList' => $this->models->district->getDistrictPair('district_status = 1'),
		);
	}

	public function delRecommendAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "SELECT recommend_path, recommend_logo FROM t_recommends WHERE recommend_id = ?";
		$pathInfo = $this->locator->db->getRow($sql, $id);
		
		$sql = "DELETE FROM t_recommends WHERE recommend_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			foreach ($pathInfo as $value) {
				$filename = SYS_DIR . $value;
				if (file_exists($filename)) {
					@unlink($filename);
				}
			}

			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}

	public function videoAction()
	{
		$sql = "SELECT * FROM t_videos ORDER BY video_id DESC";
		$videoList = $this->locator->db->getAll($sql);
		if ($videoList) {
			foreach ($videoList as $key => $row) {
				$videoList[$key]['video_src'] = "http://" . $_SERVER['HTTP_HOST'] . '/video/video/' . $row['video_path'];
			}
		}

		return array(
			'videoList' => $videoList,
		);
	}

	public function videoUpdateAction()
	{
		if (!$this->funcs->isPost()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}
		if (!isset($_FILES['file'])) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$files = $_FILES['file'];
		$dir = VIO_DIR;
		foreach ($files['name'] as $key => $value) {
			if ($files['error'][$key] 
				|| !in_array($files['type'][$key], $this->videoType)
				|| $files['type'][$key] > $this->vioMaxSize) {
				continue;
			}

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
			move_uploaded_file($files['tmp_name'][$key], $uploadFilePath);

			$sql = "INSERT INTO t_videos SET video_path = ?";
			$this->locator->db->exec($sql, $uploadFile);
		}

		$this->funcs->redirect($this->helpers->url('basic/video'));
	}

	public function delVideoAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "SELECT video_path FROM t_videos WHERE video_id = ?";
		$path = $this->locator->db->getOne($sql, $id);
		
		$sql = "DELETE FROM t_videos WHERE video_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			$filename = VIO_DIR . $path;
			if (file_exists($filename)) {
				@unlink($filename);
			}

			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}

	public function scoreLevelAction()
	{
		$sql = "SELECT * FROM t_customer_score_level 
				WHERE level_status = 1 
				ORDER BY level_score ASC";
		$list = $this->locator->db->getAll($sql);

		if ($this->funcs->isAjax()) {
			if (!$_POST) {
				return new JsonModel('error', '请添加积分级别');
			}


			$sqlInsert = "INSERT INTO t_customer_score_level 
					SET level_name = :level_name, 
					level_score = :level_score";

			$sqlUpdate = "UPDATE t_customer_score_level 
					SET level_name = :level_name, 
					level_score = :level_score
					WHERE level_id = :level_id";
			
			foreach ($_POST['level_name'] as $key => $value) {
				if (!trim($value)) {
					return new JsonModel('error', '请输入级别名称');
				}

				$score = (int) $_POST['level_score'][$key];
				if ($score < 0) {
					return new JsonModel('error', '请输入所需积分');
				}

				$sql = "SELECT COUNT(*) FROM t_customer_score_level WHERE level_name = ?";
				$nameNum = $this->locator->db->getOne($sql, $value);

				$sql = "SELECT COUNT(*) FROM t_customer_score_level WHERE level_score = ?";
				$scoreNum = $this->locator->db->getOne($sql, $score);

				if ($_POST['level_id'][$key]) {
					if ($nameNum > 1) {
						return new JsonModel('error', '级别名称已存在');
					}
					if ($scoreNum > 1) {
						return new JsonModel('error', '所需积分段已存在');
					}
				} else {
					if ($nameNum > 0) {
						return new JsonModel('error', '级别名称已存在');
					}
					if ($scoreNum > 0) {
						return new JsonModel('error', '所需积分段已存在');
					}
				}

				if ($_POST['level_id'][$key]) {
					$this->locator->db->exec($sqlUpdate, array(
						'level_name' => trim($value),
						'level_score' => (int) $_POST['level_score'][$key] ? : 0,
						'level_id' => $_POST['level_id'][$key],
					));
				} else {
					$this->locator->db->exec($sqlInsert, array(
						'level_name' => trim($value),
						'level_score' => (int) $_POST['level_score'][$key] ?: 0,
					));
				}
			}
			
			return JsonModel::init('ok', '保存成功')->setRedirect('reload');
		}
		
		return array(
			'list' => $list,
		);
	}

	public function scoreLevelDelAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "DELETE FROM t_customer_score_level WHERE level_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}

	public function setImageSortAction()
	{
	    if (!$this->funcs->isAjax()) {
	        $this->funcs->redirect($this->helpers->url('default/index'));
	    }

	    $data = $_POST['data'];
	    $sql = "UPDATE t_images 
	            SET image_sort = :sort 
	            WHERE image_id = :id";
	    foreach ($data as $row) {
	        $this->locator->db->exec($sql, $row);
	    }

	    return JsonModel::init('ok', '')->setRedirect('reload');
	}
}