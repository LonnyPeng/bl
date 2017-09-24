<?php

namespace App\Model;

class ShopModel extends CommonModel
{
	protected $name = 't_shops s';

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
	public function getShop($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getShopById($id)
	{
	    $sql = "SELECT * FROM $this->name WHERE shop_id = ?";
	    return $this->locator->db->getRow($sql, $id);
	}
}