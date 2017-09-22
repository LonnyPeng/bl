<?php

namespace App\Model;

class TurntableModel extends CommonModel
{
	protected $name = 't_task_turntables t';

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
	public function getTurntable($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getTurntableById($id)
	{
	    $sql = "SELECT * FROM $this->name WHERE turntable_id = ?";
	    return $this->locator->db->getRow($sql, $id);
	}
}