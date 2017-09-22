<?php

namespace App\Model;

class ReadModel extends CommonModel
{
	protected $name = 't_task_reads r';

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
	public function getRead($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getReadById($readId)
	{
	    $sql = "SELECT * FROM $this->name WHERE read_id = ?";
	    return $this->locator->db->getRow($sql, $readId);
	}
}