<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class ReviewController extends AbstractActionController
{
	protected $fileConfig = array(
		'review_attr' => array(
			'unread' => '已下线', 'pending' => '举报', 'published' => '已发布',
		),
	);
	protected $imgType = array('image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png');
	protected $imgMaxSize = 1024 * 1024 * 4; //4M

	public function init() {
	    parent::init();
	}

	public function listAction()
	{
		$this->perm->check(PERM_READ);
		
		$where = array();
		if ($this->param('product_name')) {
			$where[] = sprintf("p.product_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('product_name'))) . '%'));
		}
		if ($this->param('product_code')) {
			$where[] = sprintf("p.product_code = '%s'", trim($this->param('product_code')));
		}

		$count = $this->models->product->getCount(array('setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = array(
			"*",
			"(SELECT COUNT(*) FROM t_reviews r WHERE r.product_id = p.product_id) AS reviewCount",
			"(SELECT AVG(r.review_score) FROM t_reviews r WHERE r.product_id = p.product_id) AS reviewAve",
			"(SELECT COUNT(*) FROM t_reviews r WHERE r.product_id = p.product_id AND r.review_score >= 1 AND r.review_score < 2) AS reviewCount1",
			"(SELECT COUNT(*) FROM t_reviews r WHERE r.product_id = p.product_id AND r.review_score >= 2 AND r.review_score < 3) AS reviewCount2",
			"(SELECT COUNT(*) FROM t_reviews r WHERE r.product_id = p.product_id AND r.review_score >= 3 AND r.review_score < 4) AS reviewCount3",
			"(SELECT COUNT(*) FROM t_reviews r WHERE r.product_id = p.product_id AND r.review_score >= 4 AND r.review_score < 5) AS reviewCount4",
			"(SELECT COUNT(*) FROM t_reviews r WHERE r.product_id = p.product_id AND r.review_score >= 5) AS reviewCount5",
		);

		$join = 'LEFT JOIN t_product_attr pa ON pa.attr_id = p.attr_id';
		$sqlInfo = array(
			'setJoin' => $join,
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'reviewCount DESC, reviewAve DESC, product_id DESC',
		);

		$productList = $this->models->product->getProduct($files, $sqlInfo);
		if ($productList) {
			foreach ($productList as $key => $row) {
				$productList[$key]['reviewAveStar'] = $this->getStarNum($row['reviewAve']);
			}
		}

		// print_r($productList);die;
		return array(
			'productList' => $productList,
		);
	}

	public function rowAction()
	{
		$this->perm->check(PERM_READ);

		$id = trim($this->param('id'));
		$info = $this->models->product->getProductById($id);
		if (!$info) {
			$this->funcs->redirect($this->helpers->url('review/list'));
		}

		$where = array(sprintf("r.product_id = %d", $id));
		if ($this->param('customer_name')) {
			$where[] = sprintf("p.customer_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('customer_name'))) . '%'));
		}
		if ($this->param('review_attr')) {
			$where[] = sprintf("review_attr = '%s'", trim($this->param('review_attr')));
		}

		$join = array(
			'LEFT JOIN t_customers c ON c.customer_id = r.customer_id',
		);

		$count = $this->models->review->getCount(array('setWhere' => $where, 'setJoin' => $join));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = array('r.*', 'c.customer_name');

		$sqlInfo = array(
			'setJoin' => $join,
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'r.review_score DESC, r.review_id DESC',
		);

		$reviewList = $this->models->review->getReview($files, $sqlInfo);
		if ($reviewList) {
			foreach ($reviewList as $key => $row) {
				$reviewList[$key]['review_score'] = $this->getStarNum($row['review_score']);
			}
		}

		return array(
			'reviewList' => $reviewList,
			'fileConfig' => $this->fileConfig,
		);
	}

	public function detailAction()
	{
		$id = $this->param('id');
		$info = $this->models->review->getReviewById($id);
		if (!$info) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		if ($this->funcs->isAjax()) {
			$sql = "UPDATE t_reviews 
					SET review_attr = ? 
					WHERE review_id = ?";
			$attr = trim($this->param('status'));
			$status = $this->locator->db->exec($sql, $attr, $id);
			if ($status) {
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('review/list'));
			} else {
				return new JsonModel('error', '失败');
			}
		}

		$info['review_score'] = $this->getStarNum($info['review_score']);

		// print_r($info);die;
		return array(
			'info' => $info,
			'fileConfig' => $this->fileConfig,
		);
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