<?php

namespace App\Model;

class DistrictModel extends CommonModel
{
	protected $name = 't_district d';

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
	public function getDistrict($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getDistrictById($districtId)
	{
	    $sql = "SELECT * FROM $this->name WHERE district_id = ?";
	    return $this->locator->db->getRow($sql, $districtId);
	}
}