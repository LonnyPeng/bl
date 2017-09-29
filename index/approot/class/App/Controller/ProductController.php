<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class ProductController extends AbstractActionController
{
	public $districtId = null;
	public $customerId = null;

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

		// print_r($info);die;
		return array(
			'info' => $info,
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

		$limit = array(0, 3);
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

		// print_r($reviewList);die;
		return array(
			'reviewList' => $reviewList,
		);
	}

	public function reviewDetailAction()
	{
		return array();
	}

	public function reviewEditAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "UPDATE t_reviews SET review_attr = 'pending' WHERE review_id = ? ANND review_attr";

		$status = $this->locator->db->exec($sql, $this->customerId, $id);
		if ($status) {
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
	    
	    return $number;
	}
}
