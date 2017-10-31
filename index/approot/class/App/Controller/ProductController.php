<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;
use App\Controller\Plugin\Score;

class ProductController extends AbstractActionController
{
	public $districtId = null;
	public $customerId = null;
	protected $imgType = array('image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png');
	protected $imgMaxSize = 1024 * 1024 * 4; //4M

	public function init() 
	{
	    parent::init();

	    $this->districtId = $_SESSION['customer_info']['district_id'];
	    $this->customerId = $this->locator->get('Profile')['customer_id'];
	}

	public function listAction()
	{
		$attrId = trim($this->param('attr'));
		$sql = "SELECT * FROM t_product_attr WHERE attr_id = ?";
		$attrInfo = $this->locator->db->getRow($sql, $attrId);
		if (!$attrInfo) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$this->layout->title = sprintf('%s列表', $attrInfo['attr_name']);

		$limit = array(0, 2);
		$where = array(
		    'p.product_status = 1',
		    sprintf("p.district_id = %d", $this->districtId),
		    sprintf("p.attr_id = %d", $attrInfo['attr_id']),
		);

		if ($this->funcs->isAjax() && $this->param('type') == 'page') {
		    $limit[0] = $this->param('pageSize') * $limit[1];
		}
		
		$sqlInfo = array(
		    'setWhere' => $where,
		    'setLimit' => $limit,
		    'setOrderBy' => 'product_sort DESC, product_id DESC',
		);

		$productList = $this->models->product->getProduct('*', $sqlInfo);
		if ($productList) {
		    foreach ($productList as $key => $row) {
		        $productList[$key] = $this->models->product->getProductById($row['product_id']);
		    }
		}

		// print_r($productList);die;
		if ($this->funcs->isAjax() && $this->param('type') == 'page') {
		    if ($productList) {
		        foreach ($productList as $key => $row) {
		            foreach ($row as $ke => $value) {
		                if (!$value) {
		                    $productList[$key][$ke] = '';
		                }
		            }

		            $productList[$key]['image_path'] = (string) $this->helpers->uploadUrl($row['images']['home'][0]['image_path'], 'product');
		            $productList[$key]['product_price'] = $this->funcs->showValue($row['product_price']);
		            $productList[$key]['product_virtual_price'] = sprintf("%.2f", $row['product_virtual_price']);
		            $productList[$key]['collection_num'] = $row['collection_num'] ?: 0;
		            $productList[$key]['collection_status'] = $row['collection_id'] ? 'on' : '';
		            $productList[$key]['href'] = (string) $this->helpers->url('product/detail', array('id' => $row['product_id']));
		            $productList[$key]['button'] = $row['product_type'] == 2 ? '组团领' : '立即白领';
		        }

		        return JsonModel::init('ok', '', $productList);
		    } else {
		        return new JsonModel('error', '');
		    }
		} else {
		    return array(
		        'productList' => $productList,
		        'attrList' => $this->models->product->getAttrPair(),
		    );
		}
	}

	public function detailAction()
	{
		$id = trim($this->param('id'));
		$info = $this->models->product->getProductById($id);
		if (!$info) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		if ($info['product_type'] == 2) {
			//组团
			$this->layout->title = '组团白领详情页';
		} else {
			//白领
			$this->layout->title = $info['product_logo_name'];
		}

		//获取评论
		$where = array(
			"r.review_attr <> 'unread'",
			sprintf("r.product_id = %d", $id),
		);
		$join = 'LEFT JOIN t_customers c ON c.customer_id = r.customer_id';
		$files = array('r.*', 'c.*');

		$sqlInfo = array(
			'setJoin' => $join,
			'setWhere' => $where,
			'setLimit' => '0, 5',
			'setOrderBy' => 'review_id DESC',
		);

		$reviewList = $this->models->review->getReview($files, $sqlInfo);
		if ($reviewList) {
			foreach ($reviewList as $key => $row) {
				$row = $this->models->review->getReviewById($row['review_id']);
				$row['review_score'] = $this->getStarNum($row['review_score']);
				$reviewList[$key] = $row;
			}
		}

		$sql = "SELECT COUNT(*) 
				FROM t_reviews 
				WHERE review_attr <> 'unread' 
				AND product_id = ?";
		$reviewCount = $this->locator->db->getOne($sql, $id);

		// print_r($info);die;
		return array(
			'info' => $info,
			'reviewList' => $reviewList,
			'reviewCount' => $reviewCount,
		);
	}

	public function collectionAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$type = $this->param('type');
		if ($type == 'del') {
			$sql = "DELETE FROM t_customer_collections WHERE customer_id = ? AND product_id = ?";
		} else {
			$sql = "INSERT INTO t_customer_collections SET customer_id = ?, product_id = ?";
		}
		
		$status = $this->locator->db->exec($sql, $this->customerId, $id);
		if ($status) {
			return JsonModel::init('ok', '');
		} else {
			return new JsonModel('error', '失败');
		}
	}

	public function reviewAction()
	{
		$id = trim($this->param('id'));
		$info = $this->models->product->getProductById($id);
		if (!$info) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$this->layout->title = '评价';

		$limit = array(0, 5);
		if ($this->funcs->isAjax() && $this->param('type') == 'page') {
		    $limit[0] = $this->param('pageSize') * $limit[1];
		}

		$where = array(
			"r.review_attr <> 'unread'",
			sprintf("r.product_id = %d", $id),
		);
		$join = 'LEFT JOIN t_customers c ON c.customer_id = r.customer_id';
		$files = array('r.*', 'c.*');

		$sqlInfo = array(
			'setJoin' => $join,
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'review_id DESC',
		);

		$reviewList = $this->models->review->getReview($files, $sqlInfo);
		if ($reviewList) {
			foreach ($reviewList as $key => $row) {
				$row = $this->models->review->getReviewById($row['review_id']);
				$row['review_score'] = $this->getStarNum($row['review_score']);
				$reviewList[$key] = $row;
			}
		}

		if ($this->funcs->isAjax() && $this->param('type') == 'page') {
		    if ($reviewList) {
		        foreach ($reviewList as $key => $row) {
		            foreach ($row as $ke => $value) {
		                if (!$value) {
		                    $reviewList[$key][$ke] = '';
		                }
		            }

		            $reviewList[$key]['href'] = (string) $this->helpers->url('product/review-detail', array('id' => $row['review_id']));
		            $reviewList[$key]['customer_headimg'] = (string) $this->helpers->uploadUrl($row['customer_headimg'], 'user');
		            $reviewList[$key]['review_score'] = sprintf("%d%%", $row['review_score']);
		            $reviewList[$key]['review_time'] = date("Y-m-d", strtotime($row['review_time']));
		            if ($row['images']) {
		            	foreach ($row['images'] as $k => $r) {
		            		$reviewList[$key]['images'][$k]['image_path'] = (string) $this->helpers->uploadUrl($r['image_path'], 'review');
		            	}
		            }
		            $reviewList[$key]['log_id'] = $row['log_id'] ? 'on' : '';
		        }

		        return JsonModel::init('ok', '', $reviewList);
		    } else {
		        return new JsonModel('error', '');
		    }
		} else {
		    return array(
		    	'reviewList' => $reviewList,
		    );
		}
	}

	public function reviewDetailAction()
	{
		$this->layout->title = "评论详情";

		$id = trim($this->param('id'));
		$info = $this->models->review->getReviewById($id);
		if (!$info) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$info['review_score'] = $this->getStarNum($info['review_score']);

		return array(
			'info' => $info,
		);
	}

	public function reviewEditAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "UPDATE t_reviews 
				SET review_attr = 'pending' 
				WHERE review_id = ? 
				AND review_attr = 'published'";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			return JsonModel::init('ok', '已经成功举报该评论!');
		} else {
			return new JsonModel('error', '举报失败');
		}
	}

	public function reviewAddAction()
	{
		$this->layout->title = "添加评论";
		$this->layout->style = "background:#FFF;";
		
		$id = trim($this->param('id'));
		$where = array(
			sprintf("order_id = %d", $id),
			"order_type = 'received'",
		);
		$info = $this->models->order->getOrderInfo($where);
		if (!$info && !$this->param('status')) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		if ($this->funcs->isPost()) {
			//添加评论
			$map = array(
				'customer_id' => $this->customerId,
				'product_id' => $info['product_id'],
				'review_content' => trim($_POST['review_content']),
				'review_score' => trim($_POST['review_score']),
			);
			$sql = "INSERT INTO t_reviews 
					SET customer_id = :customer_id,
					 product_id = :product_id, 
					 review_content = :review_content, 
					 review_score = :review_score,
					 review_attr = 'published'";
			$status = $this->locator->db->exec($sql, $map);
			$reviewId = $this->locator->db->lastInsertId();

			//保存图片
			if (isset($_FILES)) {
				$images = $this->saveReviewImg($_FILES);
				foreach ($images as $key => $row) {
					//处理图片
					$result = $this->funcs->setImage(REVIEW_DIR . $row['image_path'], REVIEW_DIR, 800, 800);
					if (!$result['status']) {
						return new JsonModel('error', $result['content']);
					} else {
						$images[$key]['image_path'] = $result['content'];
					}
				}

				$sql = "INSERT INTO t_review_images 
						SET review_id = :review_id, 
						image_path = :image_path";
				foreach ($images as $row) {
					$this->locator->db->exec($sql, array(
						'review_id' => $reviewId,
						'image_path' => $row['image_path'],
					));
				}
			}

			//修改订单状态
			$sql = "UPDATE t_orders 
					SET order_type = 'review' 
					WHERE order_type = 'received' 
					AND order_id = ?";
			$status = $this->locator->db->exec($sql, $id);

			//改变积分 获取评论10积分
			$status = $this->score(array(
				'type' => 'have', 
				'des' => Score::PLSP, 
				'score' => 10,
			));

			$this->funcs->redirect($this->helpers->url('product/review-add', array('id' => $id, 'status' => 'ok')));
		}

		// print_r($info);die;
		return array();
	}

	protected function saveReviewImg($files = array())
	{
		$dir = REVIEW_DIR;
		$data = array();
		foreach ($files as $row) {
			if ($row['error'] || !in_array($row['type'], $this->imgType) || $row['size'] > $this->imgMaxSize) {
				continue;
			}

			$filename = $row['tmp_name'];
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
				$data[] = array(
					'image_path' => $uploadFile,
				);
			}
		}

		return $data;
	}

	public function reviewUpAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');

		//判断是否赞过评论
		$sql = "SELECT log_id 
				FROM t_review_logs 
				WHERE review_id = ? 
				AND customer_id = ?";
		if ($this->locator->db->getOne($sql, $id, $this->customerId)) {
			return new JsonModel('error', '你已赞过该评论');
		}

		$sql = "UPDATE t_reviews 
				SET review_vote_up = review_vote_up + 1 
				WHERE review_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			$sql = "INSERT INTO t_review_logs 
					SET review_id = :review_id, 
					customer_id = :customer_id";
			$this->locator->db->exec($sql, array(
				'review_id' => $id,
				'customer_id' => $this->customerId,
			));
			return JsonModel::init('ok', '');
		} else {
			return new JsonModel('error', '失败');
		}
	}

	public function reviewDownAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');

		//判断是否赞过评论
		$sql = "SELECT log_id 
				FROM t_review_logs 
				WHERE review_id = ? 
				AND customer_id = ?";
		$logId = $this->locator->db->getOne($sql, $id, $this->customerId);
		if (!$logId) {
			return new JsonModel('error', '你还没赞过该评论');
		}

		$sql = "UPDATE t_reviews 
				SET review_vote_up = review_vote_up - 1 
				WHERE review_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			$sql = "DELETE FROM t_review_logs 
					WHERE log_id = ?";
			$this->locator->db->exec($sql, $logId);
			return JsonModel::init('ok', '');
		} else {
			return new JsonModel('error', '失败');
		}
	}

	protected function getStarNum($value = '')
	{
	    $number = 0; 
	    for ($i = 0; $i < 5; $i++) {
	        if ($value > ($i + 0.5)) {
	            $number++;
	        } elseif ($value > $i) {
	            $number += 0.5;
	        }
	    }
	    
	    return $number * 20;
	}
}
