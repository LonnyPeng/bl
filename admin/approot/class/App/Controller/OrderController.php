<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class OrderController extends AbstractActionController
{
	public function init() {
	    parent::init();
	}

	public function listAction()
	{
		return array();
	}

	public function editAction()
	{
		return array();
	}

	public function groupListAction()
	{
		$where = array();

		$count = $this->models->orderGroup->getCount(array('setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = 'og.*, p.product_name';
		$sqlInfo = array(
			'setJoin' => 'LEFT JOIN t_products p ON p.product_id = og.product_id',
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'group_id DESC',
		);

		$groupList = $this->models->orderGroup->getOrderGroup($files, $sqlInfo);

		// print_r($groupList);die;
	    return array(
	    	'groupList' => $groupList,
	    );
	}

	public function groupDelAction()
	{
		if (!$this->funcs->isAjax()) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		$id = $this->param('id');
		$sql = "DELETE FROM t_order_groups WHERE group_id = ?";
		$status = $this->locator->db->exec($sql, $id);
		if ($status) {
			return JsonModel::init('ok', '删除成功');
		} else {
			return new JsonModel('error', '删除失败');
		}
	}
}