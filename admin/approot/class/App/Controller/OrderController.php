<?php

namespace App\Controller;

use Framework\View\Model\JsonModel;

class OrderController extends AbstractActionController
{
	public $filed = array(
		'shinging_type' => array('self' => '自提', 'logistics' => '配送'),
		'order_type' => array('group' => '组团中', 'pending' => '待发货', 'shipped' => '已发货', 'received' => '已到货', 'review' => '已评论'),
	);

	public function init() {
	    parent::init();
	}

	public function listAction()
	{
		$this->perm->check(PERM_READ);

		$where = array();
		if ($this->param('order_number')) {
			$where[] = sprintf("o.order_number LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('order_number'))) . '%'));
		}
		if ($this->param('product_name')) {
			$where[] = sprintf("o.product_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('product_name'))) . '%'));
		}
		if ($this->param('product_attr_id')) {
			$where[] = sprintf("p.attr_id = %d", trim($this->param('product_attr_id')));
		}
		if ($this->param('district_id')) {
			$where[] = sprintf("p.district_id = %d", trim($this->param('district_id')));
		}
		if ($this->param('customer_name')) {
			$where[] = sprintf("c.customer_name LIKE '%s'", addslashes('%' . $this->helpers->escape(trim($this->param('customer_name'))) . '%'));
		}
		if ($this->param('start')) {
			$where[] = sprintf("o.order_time >= '%s'",  date("Y-m-d 00:00:00", strtotime($this->param('start'))));
		}
		if ($this->param('end')) {
			$where[] = sprintf("o.order_time < '%s'", date("Y-m-d 00:00:00", strtotime($this->param('end') . " + 1 days")));
		}
		if ($this->param('district_id')) {
			$where[] = sprintf("p.district_id = %d", trim($this->param('district_id')));
		}
		if ($this->param('order_type')) {
			$where[] = sprintf("o..order_type = '%s'", trim($this->param('order_type')));
		}
		
		$join = array(
			"LEFT JOIN t_customers c ON c.customer_id = o.customer_id",
			"LEFT JOIN t_products p ON p.product_id = o.product_id",
			"LEFT JOIN t_product_attr pa ON pa.attr_id = p.attr_id",
			"LEFT JOIN t_district d ON d.district_id = p.district_id",
		);

		$count = $this->models->order->getCount(array('setJoin' => $join, 'setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = array('o.*', 'c.customer_name', 'pa.attr_name', 'd.district_name AS city_name');
		$sqlInfo = array(
			'setJoin' => $join,
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'order_id DESC',
		);

		$orderList = $this->models->order->getOrder($files, $sqlInfo);

		return array(
			'attrList' => $this->models->product->getAttrPair(),
			'districtList' => $this->models->district->getDistrictSelect(),
			'orderList' => $orderList,
			'filed' => $this->filed,
		);
	}

	public function detailAction()
	{
		$id = $this->param('id');
		$info = $this->models->order->getOrderById($id);
		if (!$info) {
			$this->funcs->redirect($this->helpers->url('default/index'));
		}

		if ($this->funcs->isAjax()) {
			$sql = "UPDATE t_orders 
					SET order_type = 'shipped',
					order_shipped_time = now() 
					WHERE order_id = ?
					AND order_type = 'pending'";
			$status = $this->locator->db->exec($sql, $id);
			if ($status) {
				return JsonModel::init('ok', '成功')->setRedirect($this->helpers->url('order/list'));
			} else {
				return new JsonModel('error', '失败');
			}
		}

		// print_r($info);die;
		return array(
			'info' => $info,
			'filed' => $this->filed,
		);
	}

	public function groupListAction()
	{
		$this->perm->check(PERM_READ);
		
		$where = array();
		if ($this->param('product_attr_id')) {
			$where[] = sprintf("p.attr_id = %d", trim($this->param('product_attr_id')));
		}
		if ($this->param('district_id')) {
			$where[] = sprintf("p.district_id = %d", trim($this->param('district_id')));
		}

		$join = array(
			"LEFT JOIN t_products p ON p.product_id = og.product_id",
			"LEFT JOIN t_product_attr pa ON pa.attr_id = p.attr_id",
			"LEFT JOIN t_district d ON d.district_id = p.district_id",
		);

		$count = $this->models->orderGroup->getCount(array('setJoin' => $join, 'setWhere' => $where));
		$this->helpers->paginator($count, 10);
		$limit = array($this->helpers->paginator->getLimitStart(), $this->helpers->paginator->getItemCountPerPage());

		$files = 'og.*, p.product_name, d.district_name AS city_name, pa.attr_name, p.product_group_time';
		$sqlInfo = array(
			'setJoin' => $join,
			'setWhere' => $where,
			'setLimit' => $limit,
			'setOrderBy' => 'group_id DESC',
		);

		$groupList = $this->models->orderGroup->getOrderGroup($files, $sqlInfo);
		if ($groupList) {
			foreach ($groupList as $key => $row) {
				$groupList[$key]['time'] = (strtotime($row['group_time']) + $row['product_group_time'] * 86400) - time();
			}
		}

		// print_r($groupList);die;
	    return array(
	    	'attrList' => $this->models->product->getAttrPair(),
	    	'districtList' => $this->models->district->getDistrictSelect(),
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