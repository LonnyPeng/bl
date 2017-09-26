<?php

namespace App\Model;

class OrderModel extends CommonModel
{
	protected $name = 't_orders o';

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
	public function getOrder($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getOrderById($id)
	{
	    $sql = "SELECT * FROM $this->name WHERE order_id = ?";
	    $info = $this->locator->db->getRow($sql, $id);
	    return $info;
	}
}