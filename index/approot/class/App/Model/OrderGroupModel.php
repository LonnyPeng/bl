<?php

namespace App\Model;

class OrderGroupModel extends CommonModel
{
	protected $name = 't_order_groups og';

	/**
	 * Get count
	 *
	 */
	public function getCount($sqlInfo)
	{
	    $sql = "SELECT COUNT(*) FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getOne($sql);
	}

	/**
	 * Get list
	 *
	 */
	public function getOrderGroup($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);
	    $result = $this->locator->db->getAll($sql);
	    if ($result) {
	    	foreach ($result as $key => $row) {
	    		$row['group_type'] = $this->getStatus($row);
	    		$row['customer_id'] = $this->getCustomer($row['customer_id']);
	    		$result[$key] = $row;
	    	}
	    }

	    return $result;
	}

	public function getOrderGroupById($id)
	{
	    $sql = "SELECT * FROM $this->name WHERE group_id = ?";
	    $info = $this->locator->db->getRow($sql, $id);
	    if ($info) {
	    	$info['group_type'] = $this->getStatus($info);
	    	$info['customer_id'] = $this->getCustomer($info['customer_id']);
	    }
	    return $info;
	}

	protected function getStatus($row = array())
	{
		$productId = $row['product_id'];
		$sql = "SELECT * FROM t_products WHERE product_id = ?";
		$productInfo = $this->locator->db->getRow($sql, $productId);

		$num = $productInfo['product_group_num'] - count(explode(",", $row['customer_id']));
		if ($num == 0) {
			return array('status' => 'success', 'msg' => '组团成功', 'data' => array('product_group_num' => $productInfo['product_group_num']));
		} elseif (time() > strtotime(sprintf("%s + %d days", $row['group_time'], $productInfo['product_group_time']))) {
			return array('status' => 'error', 'msg' => '组团失败', 'data' => array('product_group_num' => $productInfo['product_group_num']));
		} elseif ($num > 0) {
			return array('status' => 'pending', 'msg' => sprintf("待成团，还差%d人", $num), 'data' => array('product_group_num' => $productInfo['product_group_num']));
		}
	}

	protected function getCustomer($ids)
	{
		$data = array();

		$ids = explode(",", $ids);
		$sql = "SELECT * FROM t_customers WHERE customer_id = ?";
		foreach ($ids as $value) {
			$data[] = $this->locator->db->getRow($sql, $value);
		}
		
		return $data;
	}
}