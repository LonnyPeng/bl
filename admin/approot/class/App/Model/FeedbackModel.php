<?php

namespace App\Model;

class FeedbackModel extends CommonModel
{
	protected $name = 't_customer_feedbacks cf';

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
	public function getFeedback($files = '*', $sqlInfo = array())
	{
	    $files = $this->setFile($files);
	    $sql = "SELECT $files FROM $this->name";
	    $sql = $this->setSql($sql, $sqlInfo);

	    return $this->locator->db->getAll($sql);
	}

	public function getFeedbackById($id)
	{
	    $sql = "SELECT * FROM $this->name WHERE feedback_id = ?";
	    return $this->locator->db->getRow($sql, $id);
	}
}