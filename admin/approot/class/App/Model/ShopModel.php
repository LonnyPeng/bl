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

	public function getShopGoupByDistrict()
	{
		$data = array();
		$sql = "SELECT * FROM $this->name WHERE shop_status = 1";
		$shops = $this->locator->db->getAll($sql);
		if ($shops) {
			foreach ($shops as $key => $row) {
				if (isset($data[$row['district_id']])) {
					$data[$row['district_id']][] = $row;
				} else {
					$data[$row['district_id']] = array($row);
				}
			}
		}

		return $data;
	}
}