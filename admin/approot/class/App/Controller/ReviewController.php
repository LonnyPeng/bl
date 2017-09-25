<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class ReviewController extends AbstractActionController
{
	protected $fileConfig = array(
		'review_attr' => array(
			'unread' => '已下线', 'pending' => '审核中', 'published' => '已发布',
		),
	);
	protected $imgType = array('image/jpeg', 'image/x-png', 'image/pjpeg', 'image/png');
	protected $imgMaxSize = 1024 * 1024 * 4; //4M

	public function init() {
	    parent::init();
	}

	public function listAction()
	{
		$where = array();
		if ($this->param('customer_name')) {
			$where[] = sprintf("p.customer_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('customer_name'))) . '%'));
		}
		if ($this->param('product_name')) {
			$where[] = sprintf("p.product_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('product_name'))) . '%'));
		}

		$join = array(
			'LEFT JOIN t_customers c ON c.customer_id = r.customer_id',
			'LEFT JOIN t_products p ON p.product_id = r.product_id',
		);

		$count = $this->models->review->getCount(array('setWhere' => $where, 'setJoin' => $join));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = array('r.*', 'c.customer_name', 'p.product_name');

		$sqlInfo = array(
			'setJoin' => $join,
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'product_id DESC',
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