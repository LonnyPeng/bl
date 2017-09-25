<?php

namespace App\Model;

class PrizeModel extends CommonModel
{
	protected $name = 't_prizes p';

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
	public function getPrize($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getPrizeById($id)
	{
	    $sql = "SELECT p.*, c.customer_name, t.turntablep_title, t.turntablep_attr
	    		FROM $this->name 
	    		LEFT JOIN t_customers c ON c.customer_id = p.customer_id
	    		LEFT JOIN t_turntable_products t ON t.turntablep_id = p.turntablep_id
	    		WHERE prize_id = ?";
	    return $this->locator->db->getRow($sql, $id);
	}
}